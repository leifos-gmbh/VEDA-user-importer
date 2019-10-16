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
	 * @param int $obj_id
	 * @param int $usr_id
	 * @param int $status
	 */
	public function handleTrackingEvent(int $obj_id, int $usr_id, int $status)
	{
		if($status != ilLPStatus::LP_STATUS_COMPLETED_NUM) {
			$this->logger->debug('Ignoring non completed event.');
			return false;
		}
		$usr_oid = \ilObjUser::_lookupImportId($usr_id);
		if(!$usr_oid) {
			$this->logger->debug('Not imported user.');
			return false;
		}
		if(\ilObject::_lookupType($obj_id) != 'exc') {
			$this->logger->debug('Ignoring non session event');
			return false;
		}


		$refs = ilObject::_getAllReferences($obj_id);
		$ref = end($refs);

		$segment_id = $this->mdhelper->findTrainSegmentId($ref);

		if(!$segment_id) {
			$this->logger->debug('Not ausbildungszugabschnitt');
			return false;
		}

		try {
			$connector = \ilVedaConnector::getInstance();
			$connector->sendExerciseSuccess($segment_id, $usr_oid);
		}
		catch(\ilVedaConnectionException $e) {
			$this->logger->warning('Update exercise success failed.');
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

		$course_ref_id = $this->mdhelper->findTrainingCourseTrain($oid);
		$course = \ilObjectFactory::getInstanceByRefId($course_ref_id);
		if(!$course instanceof \ilObjCourse) {
			throw new \ilVedaMemberImportException('Cannot find course for oid: ' . $oid);
		}
		$participants = \ilParticipants::getInstance($course_ref_id);
		if(!$participants instanceof \ilCourseParticipants) {
			throw new \ilVedaMemberImportException('Cannot find course participants for oid: ' . $oid);
		}

		$this->logger->debug('Handling course: ' . $course->getTitle());
		$this->logger->dump($members, \ilLogLevel::DEBUG);

		$status = new \ilVedaCourseStatus($oid);

		$currently_assigned = $participants->getParticipants();

		$this->removeInvalidRegularMembers($course, $participants, $members, $status, $currently_assigned);
		$this->removeInvalidPermanentSwitchMembers($course, $participants, $members, $status, $currently_assigned);
		$this->removeInvalidTemporarySwitchMembers($course, $participants, $members, $status, $currently_assigned);

		$this->addRegularMembers($course, $participants, $members, $status, $currently_assigned);
		$this->addPermanentSwitchMembers($course, $participants, $members, $status, $currently_assigned);
		$this->addTemporarySwitchMembers($course, $participants, $members, $status, $currently_assigned);
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $part
	 * @param array $members
	 * @param \ilVedaCourseStatus $status
	 * @param int[] $assigned
	 */
	protected function removeInvalidRegularMembers(
		\ilObjCourse $course,
		\ilCourseParticipants $part,
		array $members,
		\ilVedaCourseStatus $status,
		array $assigned)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();
		$review = $DIC->rbac()->review();

		foreach($review->assignedUsers($course->getDefaultMemberRole()) as $participant) {

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
	 * @param int[] $assigned
	 */
	protected function removeInvalidPermanentSwitchMembers(
		\ilObjCourse $course,
		\ilCourseParticipants $part,
		array $members,
		\ilVedaCourseStatus $status,
		array $assigned)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();
		$review = $DIC->rbac()->review();

		foreach($review->assignedUsers($status->getPermanentSwitchRole()) as $participant) {

			$oid = \ilObjUser::_lookupImportId($participant);
			if(!$oid) {
				$this->logger->debug('Ignoring non imported user.');
				continue;
			}

			$found = false;
			/** @var $members AusbildungszugTeilnehmer[] **/
			foreach($members as $member) {
				if($member->getTeilnehmerId() != $oid) {
					continue;
				}
				if(
					$member->getMitgliedschaftsart() == self::REGULAR &&
					$member->getWechsel() &&
					$this->isValidDate($member->getBeginn(), $member->getEnde())
				) {
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
	 * @param int[] $assigned
	 */
	protected function removeInvalidTemporarySwitchMembers(
		\ilObjCourse $course,
		\ilCourseParticipants $part,
		array $members,
		\ilVedaCourseStatus $status,
		array $assigned)
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
	 * @param int[] $assigned
	 */
	protected function addRegularMembers(
		\ilObjCourse $course,
		\ilCourseParticipants $part,
		array $members,
		\ilVedaCourseStatus $status,
		array $assigned)
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
				$this->assignUserToRole(
					$course->getDefaultMemberRole(),
					$uid,
					$assigned,
					$part,
					$course
				);
			}
		}
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $part
	 * @param array $members
	 * @param \ilVedaCourseStatus $status
	 * @param int[] $assigned
	 */
	protected function addPermanentSwitchMembers(
		\ilObjCourse $course,
		\ilCourseParticipants $part,
		array $members,
		\ilVedaCourseStatus $status,
		array $assigned)
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
				$this->assignUserToRole(
					$status->getPermanentSwitchRole(),
					$uid,
					$assigned,
					$part,
					$course
				);
			}
		}
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $part
	 * @param array $members
	 * @param \ilVedaCourseStatus $status
	 * @param int[] $assigned
	 */
	protected function addTemporarySwitchMembers(
		\ilObjCourse $course,
		\ilCourseParticipants $part,
		array $members,
		\ilVedaCourseStatus $status,
		array $assigned)
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
				$this->assignUserToRole(
					$status->getTemporarySwitchRole(),
					$uid,
					$assigned,
					$part,
					$course
				);
			}
		}
	}

	/**
	 * @param int $role
	 * @param int $user
	 * @param array $assigned
	 * @param \ilCourseParticipants $part
	 */
	protected function assignUserToRole(int $role, int $user, array &$assigned, \ilCourseParticipants $part, \ilObjCourse $course)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();
		$admin->assignUser($role, $user);

		if(!in_array($user, $assigned)) {

			$this->logger->debug('Adding new user sending mail notification...');
			$part->sendNotification($part->NOTIFY_ACCEPT_USER, $user);
			\ilObjUser::_addDesktopItem($user, $course->getId(), 'crs');
			$assigned[] = $user;
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