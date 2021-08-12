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

		// additional check in user status table
		$us = new \ilVedaUserStatus($usr_oid);
		if(!$usr_oid) {
			$this->logger->debug('Not imported user.');
			return false;
		}
		if($us->getCreationStatus() != \ilVedaUserStatus::STATUS_SYNCHRONIZED) {
			$this->logger->info('Ignoring not synchronized user account: ' . $usr_oid);
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
		$this->sendExerciseSuccessInformation($obj_id, $usr_id, $usr_oid, $segment_id);
	}

    /**
     * @param int    $obj_id
     * @param int    $usr_id
     * @param string $usr_oid
     * @param string $segment_id
     * @throws ilDatabaseException
     */
	protected function sendExerciseSuccessInformation(int $obj_id, int $usr_id, string $usr_oid, string $segment_id)
	{
		global $DIC;

		$tree = $DIC->repositoryTree();

		// find parent courses
		$exercise = \ilObjectFactory::getInstanceByObjId($obj_id, false);
		if(!$exercise instanceof \ilObjExercise) {
			$this->logger->warning('Cannot create exercise instance');
			return;
		}


		// find ref_ids for exercise
		$refs = \ilObject::_getAllReferences($exercise->getId());

		$is_practical_training = false;
		$is_self_learning = false;
		$submission_date_str = '';
		foreach($refs as $tmp => $ref_id) {

			$segment_id = $this->mdhelper->findTrainSegmentId($ref_id);
			$this->logger->debug('Current ref_id: ' . $ref_id . ' has segment_id: ' . $segment_id);
			if(ilVedaSegmentInfo::isPracticalTraining($segment_id)) {
				$this->logger->info('Exercise of type "practical training"');
				$is_practical_training = true;
			}
			elseif (\ilVedaSegmentInfo::isSelfLearning($segment_id)) {
			    $this->logger->info('Exercise of type "self learning"');
			    $is_self_learning = true;
            }
			else {
				$this->logger->info('No practical training type, no self learning type');
				break;
			}
			$assignments = \ilExAssignment::getInstancesByExercise($exercise->getId());
			foreach($assignments as $assignment) {

				$submission = new \ilExSubmission($assignment, $usr_id);
				$submission_date_str = $submission->getLastSubmission();
				$this->logger->notice('Last submission is: ' . $submission_date_str);
			}
			break;
		}

		if ($is_practical_training && $submission_date_str) {
		    try {
                $connector = \ilVedaConnector::getInstance();
                $submission_date = new DateTime($submission_date_str);
                $connector->sendExerciseSubmissionDate($segment_id, $usr_oid, $submission_date);
                $connector->sendExerciseSubmissionConfirmed($segment_id, $usr_oid, new \DateTime());
                $connector->sendExerciseSuccess($segment_id, $usr_oid, new \DateTime());
            }
            catch (\ilVedaConnectionException $e) {
		        $this->logger->error('Send exercise success failed with message: ' . $e->getMessage());
            }
        }
		elseif ($is_practical_training) {
		    $this->logger->notice('Did not send exercise success messages for user without submission. ');
		    $this->logger->notice('User id: ' . $usr_id);
		    $this->logger->notice('Exercise ref_id: ' . $ref_id);
        }
		if ($is_self_learning) {
		    try {
		        $connector = \ilVedaConnector::getInstance();
		        $connector->sendExerciseSuccess($segment_id, $usr_oid, new \DateTime());
            }
            catch (\ilVedaConnectionException $e) {
		        $this->logger->error('Send exercise success for type "self training" failed with message: ' . $e->getMessage());
            }
        }
		return;
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

		$this->handleTutorAssignments($course, $participants, $oid);
	}

	/**
	 * @param \ilObjCourse $course
	 * @param \ilCourseParticipants $participants
	 */
	protected function handleTutorAssignments(\ilObjCourse $course, \ilCourseParticipants $participants, ?string $oid)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();

		$udfplugin = $this->plugin->getUDFClaimingPlugin();
		$udffields = $udfplugin->getFields();

		try {
			$connector = \ilVedaConnector::getInstance();
			$remote_tutors = $connector->readTrainingCourseTrainTutors($oid);
			$remote_companions = $connector->readTrainingCourseTrainCompanions($oid);
			$remote_supervisors = $connector->readTrainingCourseTrainSupervisors($oid);
			$this->logger->dump($remote_tutors, \ilLogLevel::DEBUG);
			$this->logger->dump($remote_companions, \ilLogLevel::DEBUG);
            $this->logger->dump($remote_supervisors, \ilLogLevel::DEBUG);
            $this->logger->debug('For course: ' . $course->getTitle());
		}
		catch(\ilVedaConnectionException $e) {
			$this->logger->warning('Reading assigned tutors failed. Aborting tutor update');
			return false;
		}
		// deassign deprecated tutors
		foreach($participants->getTutors() as $tutor_id) {

			$tutor = \ilObjectFactory::getInstanceByObjId($tutor_id,false);
			if(!$tutor instanceof \ilObjUser) {
				$this->logger->warning('Found invalid tutor: ' . $tutor_id);
				continue;
			}
			$udf_data = $tutor->getUserDefinedData();
			$tutor_oid = '';
			if(isset($udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_TUTOR_ID]])) {
				$tutor_oid = $udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_TUTOR_ID]];
			}
			$companion_oid = '';
			if(isset($udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_COMPANION_ID]])) {
				$companion_oid = $udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_COMPANION_ID]];
			}
			$supervisor_oid = '';
			if (isset($udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_SUPERVISOR_ID]])) {
			    $supervisor_id = $udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_SUPERVISOR_ID]];
            }
			if(!$tutor_oid && !$companion_oid && !$supervisor_oid) {
				$this->logger->debug('Ignoring tutor without tutor_oid: ' . $tutor->getLogin());
				continue;
			}

			$found = false;
			foreach($remote_tutors as $remote_tutor) {
			    if (!$this->isValidDate($remote_tutor->getKursZugriffAb(), $remote_tutor->getKursZugriffBis())) {
			        $this->logger->debug('Ignoring tutor outside time frame: ' .
                        $remote_tutor->getDozentId()
                    );
			        continue;
                }
			    if (\ilVedaUtils::compareOidsEqual($remote_tutor->getDozentId(),$tutor_oid)) {
					$found = true;
					break;
				}
			}
			foreach($remote_companions as $remote_companion) {
				if(!$this->isValidDate($remote_companion->getZustaendigAb(), $remote_companion->getZustaendigBis())) {
					$this->logger->debug('Ignoring companion outside time frame: ' . $remote_companion->getLernbegleiterId());
					continue;
				}
				if (\ilVedaUtils::compareOidsEqual($remote_companion->getLernbegleiterId(),$companion_oid)) {
					$found = true;
					break;
				}
			}
			foreach ($remote_supervisors as $remote_supervisor) {
			    if (!$this->isValidDate($remote_supervisor->getKursZugriffAb(), $remote_supervisor->getKursZugriffBis())) {
                    $this->logger->debug('Ignoring supervisor outside time frame: ' . $remote_supervisor->getAufsichtspersonId());
                    continue;
                }
			    if (\ilVedaUtils::compareOidsEqual($remote_supervisor->getAufsichtspersonId(), $supervisor_oid)) {
			        $found = true;
			        break;
                }
            }
			if(!$found) {
				$this->logger->info('Deassigning deprecated tutor from course: ' . $tutor->getLogin());
				$admin->deassignUser($course->getDefaultTutorRole(), $tutor_id);
				$participants->updateContact($tutor_id, false);
			}
		}
		// assign missing tutors
		foreach($remote_tutors as $remote_tutor) {

			$tutor_oid = $remote_tutor->getDozentId();
			$this->logger->debug('Remote tutor oid is: ' . $tutor_oid);
			$this->logger->dump($udfplugin->getUsersForTutorId($tutor_oid));

			foreach($udfplugin->getUsersForTutorId($tutor_oid) as $uid) {
				if(!in_array($uid, $participants->getTutors())) {
					$admin->assignUser($course->getDefaultTutorRole(), $uid);
					$participants->updateContact($uid, true);
				}
			}
		}
		// assign companions
		foreach($remote_companions as $remote_companion)
		{
			$companion_id = $remote_companion->getLernbegleiterId();
			$this->logger->debug('Remote companion oid is: ' . $companion_id);
			$this->logger->dump($udfplugin->getUsersForCompanionId($companion_id));

			if(!$this->isValidDate($remote_companion->getZustaendigAb(), $remote_companion->getZustaendigBis())) {
				$this->logger->info('Outside time frame: Ignoring companion with id: ' . $companion_id);
				continue;
			}
			foreach($udfplugin->getUsersForCompanionId($companion_id) as $uid) {
				if(!in_array($uid, $participants->getTutors())) {
					$this->logger->info('Assigning new course tutor with id: ' . $companion_id . ' ILIAS id: ' . $uid);
					$admin->assignUser($course->getDefaultTutorRole(), $uid);
					$participants->updateContact($uid, true);
				}
			}
		}
		foreach ($remote_supervisors as $remote_supervisor) {
            $supervisor_id = $remote_supervisor->getAufsichtspersonId();
            $this->logger->debug('Remote supervisor oid is: ' . $supervisor_id);
            $this->logger->dump($udfplugin->getUsersForSupervisorId($supervisor_id));

            if(!$this->isValidDate($remote_supervisor->getKursZugriffAb(), $remote_supervisor->getKursZugriffBis())) {
                $this->logger->info('Outside time frame: Ignoring supervisor with id: ' . $supervisor_id);
                continue;
            }
            foreach($udfplugin->getUsersForSupervisorId($supervisor_id) as $uid) {
                if(!in_array($uid, $participants->getTutors())) {
                    $this->logger->info('Assigning new course tutor with id: ' . $supervisor_id . ' ILIAS id: ' . $uid);
                    $admin->assignUser($course->getDefaultTutorRole(), $uid);
                    $participants->updateContact($uid, true);
                }
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
				if(strtolower($member->getTeilnehmerId()) != strtolower($oid)) {
					continue;
				}
				if(
				    $member->getMitgliedschaftsart() == self::REGULAR &&
                    !$member->getWechsel() &&
                    $this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())
                ) {
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
				if(strtolower($member->getTeilnehmerId()) != strtolower($oid)) {
					continue;
				}
				if(
					$member->getMitgliedschaftsart() == self::REGULAR &&
					$member->getWechsel() &&
					$this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())
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
				if(strtolower($member->getTeilnehmerId()) != strtolower($oid)) {
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
			if(!$this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())) {
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
			if (!$this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())) {
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
			if (!$this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())) {
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
            $favourites = new ilFavouritesManager();
            $favourites->add(
                $user,
                $course->getRefId()
            );

            $assigned[] = $user;
		}
	}

	/**
	 * @param \DateTime|null $start
	 * @param \DateTime|null $end
	 */
	public function isValidDate(?DateTime $start, ?DateTime $end)
	{
		if($start == null && $end == null) {
			return true;
		}

		$now = new \ilDate(time(), IL_CAL_UNIX);
		if ($start == null) {
		    $ilend = new \ilDateTime($end->format('Y-m-d'), IL_CAL_DATE);
		    // check ending time > now
            if (
                \ilDateTime::_after($ilend, $now, IL_CAL_DAY) ||
                \ilDateTime::_equals($ilend, $now, IL_CAL_DAY)
            ) {
                $this->logger->debug('Ending date is valid');
                return true;
            }
            $this->logger->debug('Ending date is invalid');
            return false;
        }

		if($end == null) {
            $ilstart = new \ilDate($start->format('Y-m-d'),IL_CAL_DATE);
			// check starting time <= now
			if(
			    \ilDateTime::_before($ilstart, $now , IL_CAL_DAY) ||
                \ilDateTime::_equals($ilstart, $now, IL_CAL_DAY)
            ) {
				$this->logger->debug('Starting date is valid');
				return true;
			}
			$this->logger->debug('Starting date is invalid');
			return false;
		}

        $ilstart = new \ilDate($start->format('Y-m-d'),IL_CAL_DATE);
		$ilend = new \ilDate($end->format('Y-m-d'), IL_CAL_DATE);

		if(
			\ilDateTime::_within(
				$now,
				$ilstart,
				$ilend,
				IL_CAL_DAY
			) ||
            \ilDateTime::_equals(
                $now,
                $ilend,
                \ilDateTime::DAY
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