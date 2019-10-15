<?php

use Swagger\Client\Model\AusbildungszugTeilnehmer;

/**
 * Class ilVedaMemberImportAdapter
 */
class ilVedaMemberImportAdapter
{
	private const REGULAR = 'REGULAER';
	private const TEMPORARY = 'TEMPORAER';


	/**
	 * @var null | \ilVedaConnectorPlugin
	 */
	private $plugin  = null;

	/**
	 * @var \ilLogger|null
	 */
	private $logger = null;

	/**
	 * @var \ilVedaConnectorSettings|null
	 */
	private $settings = null;


	/**
	 * @var null | \ilVedaMDHelper
	 */
	private $mdhelper = null;


	/**
	 * ilVedaMemberImportAdapter constructor.
	 */
	public function __construct()
	{
		$this->plugin = \ilVedaConnectorPlugin::getInstance();
		$this->logger = $this->plugin->getLogger();
		$this->settings = \ilVedaConnectorSettings::getInstance();
		$this->mdhelper = \ilVedaMDHelper::getInstance();
	}

	/**
	 * do import
	 */
	public function import()
	{
		$this->logger->debug('Reading "Ausbildungszüge" ...');
		foreach($this->mdhelper->findTrainingCourseTrains() as $oid) {

			$this->importTrainingCourseTrain($oid);
		}
	}

	/**
	 * @param string|null $oid
	 * @throws \ilVedaConnectionException
	 */
	protected function importTrainingCourseTrain(?string $oid)
	{
		// read member info
		$connector = \ilVedaConnector::getInstance();
		$members = $connector->readTrainingCourseTrainMembers($oid);
		$this->logger->dump($members);

		$course_ref_id = $this->mdhelper->findTrainingCourseTrain($oid);
		$course = \ilObjectFactory::getInstanceByRefId($course_ref_id);
		if(!$course instanceof \ilObjCourse) {
			throw new \ilVedaMemberImportException('Cannot find course for oid: ' . $oid);
		}
		$participants = \ilParticipants::getInstance($course_ref_id);
		if(!$participants instanceof \ilCourseParticipants) {
			throw new \ilVedaMemberImportException('Cannot find course participants for oid: ' . $oid);
		}

		$status = new \ilVedaCourseStatus($oid);


		$this->removeInvalidRegularMembers($course, $participants, $members, $status);
		$this->removeInvalidPermanentSwitchMembers($course, $participants, $members, $status);
		$this->removeInvalidTemporarySwitchMembers($course, $participants, $members, $status);

		$this->addRegularMembers($course, $participants, $members, $status);
		$this->addPermanentSwitchMembers($course, $participants, $members, $status);
		$this->addTemporarySwitchMembers($course, $participants, $members, $status);
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $part
	 * @param array $members
	 * @param \ilVedaCourseStatus $status
	 */
	protected function removeInvalidRegularMembers(\ilObjCourse $course, \ilCourseParticipants $part, array $members, \ilVedaCourseStatus $status)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();

		foreach($part->getMembers() as $participant) {

			$oid = \ilObjUser::_lookupImportId($participant);
			if(!$oid) {
				continue;
			}

			$found = false;
			/** @var $members AusbildungszugTeilnehmer[] **/
			foreach($members as $member) {
				if($member->getTeilnehmerId() != $oid) {
					continue;
				}
				if($member->getMitgliedschaftsart() == self::REGULAR && !$member->getWechsel()) {
					$found = true;
					break;
				}
			}
			if(!$found) {

				$this->logger->info('Deassigning user: ' . $participant . ' with oid '. $oid .' from course: ' . $course->getTitle());
				$admin->deassignUser(
					$course->getDefaultMemberRole(),
					$participant
				);
			}
		}
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $part
	 * @param array $members
	 * @param \ilVedaCourseStatus $status
	 */
	protected function removeInvalidPermanentSwitchMembers(\ilObjCourse $course, \ilCourseParticipants $part, array $members, \ilVedaCourseStatus $status)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();
		$review = $DIC->rbac()->review();

		foreach($review->assignedUsers($status->getPermanentSwitchRole()) as $participant) {

			$oid = \ilObjUser::_lookupImportId($participant);
			if(!$oid) {
				continue;
			}

			$found = false;
			/** @var $members AusbildungszugTeilnehmer[] **/
			foreach($members as $member) {
				if($member->getTeilnehmerId() != $oid) {
					continue;
				}
				if($member->getMitgliedschaftsart() == self::REGULAR && $member->getWechsel()) {
					$found = true;
					break;
				}
			}
			if(!$found) {

				$this->logger->info('Deassigning user: ' . $participant . ' with oid '. $oid .' from course: ' . $course->getTitle());
				$admin->deassignUser(
					$status->getPermanentSwitchRole(),
					$participant
				);
			}
		}
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $part
	 * @param array $members
	 * @param \ilVedaCourseStatus $status
	 */
	protected function removeInvalidTemporarySwitchMembers(\ilObjCourse $course, \ilCourseParticipants $part, array $members, \ilVedaCourseStatus $status)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();
		$review = $DIC->rbac()->review();

		foreach($review->assignedUsers($status->getTemporarySwitchRole()) as $participant) {

			$oid = \ilObjUser::_lookupImportId($participant);
			if(!$oid) {
				continue;
			}

			$found = false;
			/** @var $members AusbildungszugTeilnehmer[] **/
			foreach($members as $member) {
				if($member->getTeilnehmerId() != $oid) {
					continue;
				}
				if($member->getMitgliedschaftsart() == self::REGULAR && $member->getWechsel()) {
					$found = true;
					break;
				}
			}
			if(!$found) {

				$this->logger->info('Deassigning user: ' . $participant . ' with oid '. $oid .' from course: ' . $course->getTitle());
				$admin->deassignUser(
					$status->getTemporarySwitchRole(),
					$participant
				);
			}
		}
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $part
	 * @param array $members
	 * @param \ilVedaCourseStatus $status
	 */
	protected function addRegularMembers(\ilObjCourse $course, \ilCourseParticipants $part, array $members, \ilVedaCourseStatus $status)
	{
		/** @var $members AusbildungszugTeilnehmer[] **/
		foreach($members as $member) {

			if($member->getMitgliedschaftsart() != self::REGULAR) {
				$this->logger->debug('Ignoring TEMPORAER member.');
				continue;
			}
			if($member->getWechsel()) {
				$this->logger->debug('Ignoring switch membership.');
				continue;
			}
			if(!$this->isValidDate($member->getBeginn(), $member->getEnde())) {
				$this->logger->debug('Ignoring deprecated assignment.');
				continue;
			}

			$uid = $this->getUserIdForImportId($member->getTeilnehmerId());

			if($uid) {
				$this->logger->info('Assigning user: ' . $uid . ' with oid '. $member->getTeilnehmerId() .' to course: ' . $course->getTitle());
				$part->add($uid, \ilCourseConstants::CRS_MEMBER);
			}
		}
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $part
	 * @param array $members
	 * @param \ilVedaCourseStatus $status
	 */
	protected function addPermanentSwitchMembers(\ilObjCourse $course, \ilCourseParticipants $part, array $members, \ilVedaCourseStatus $status)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();

		/** @var $members AusbildungszugTeilnehmer[] **/
		foreach($members as $member) {

			if($member->getMitgliedschaftsart() != self::REGULAR) {
				$this->logger->debug('Ignoring TEMPORAER member.');
				continue;
			}
			if(!$member->getWechsel()) {
				$this->logger->debug('Ignoring regular membership.');
				continue;
			}
			if(!$this->isValidDate($member->getBeginn(), $member->getEnde())) {
				$this->logger->debug('Ignoring deprecated assignment.');
				continue;
			}

			$uid = $this->getUserIdForImportId($member->getTeilnehmerId());

			if($uid) {
				$this->logger->info('Assigning user: ' . $uid . ' with oid '. $member->getTeilnehmerId() .' to course: ' . $course->getTitle());
				$admin->assignUser($status->getPermanentSwitchRole(), $uid);
			}
		}
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $part
	 * @param array $members
	 * @param \ilVedaCourseStatus $status
	 */
	protected function addTemporarySwitchMembers(\ilObjCourse $course, \ilCourseParticipants $part, array $members, \ilVedaCourseStatus $status)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();

		/** @var $members AusbildungszugTeilnehmer[] **/
		foreach($members as $member) {

			if($member->getMitgliedschaftsart() == self::REGULAR) {
				$this->logger->debug('Ignoring permanent member.');
				continue;
			}
			if(!$this->isValidDate($member->getBeginn(), $member->getEnde())) {
				$this->logger->debug('Ignoring deprecated assignment.');
				continue;
			}

			$uid = $this->getUserIdForImportId($member->getTeilnehmerId());

			if($uid) {
				$this->logger->info('Assigning user: ' . $uid . ' with oid '. $member->getTeilnehmerId() .' to course: ' . $course->getTitle());
				$admin->assignUser($status->getTemporarySwitchRole(), $uid);
			}
		}
	}

	/**
	 * @param \DateTime|null $start
	 * @param \DateTime|null $end
	 */
	public function isValidDate(?DateTime $start, ?DateTime $end)
	{
		if($start == null) {
			return true;
		}
		if($end == null) {
			return true;
		}
		$now = new \ilDate(time(), IL_CAL_UNIX);
		$ilstart = new \ilDate($start->format('Y-m-d'),IL_CAL_DATE);
		$ilend = new \ilDate($end->format('Y-m-d'), IL_CAL_DATE);

		if(
			\ilDateTime::_within(
				$now,
				$ilstart,
				$ilend,
				IL_CAL_DAY
			)
		) {
			return true;
		}
		return false;
	}


	/**
	 * @return int
	 */
	protected function getUserIdForImportId(?string $oid)
	{
		return ilObject::_lookupObjIdByImportId($oid);
	}
}