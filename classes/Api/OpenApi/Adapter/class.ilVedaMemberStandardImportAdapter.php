<?php

use OpenAPI\Client\Model\Dozentenkurszuordnung;
use OpenAPI\Client\Model\Lernbegleiterkurszuordnung;
use OpenAPI\Client\Model\AusbildungszugTeilnehmer;
use OpenAPI\Client\Model\Teilnehmerkurszuordnung;

class ilVedaMemberStandardImportAdapter
{
    /**
     * @var string
     */
    protected const REGULAR = 'REGULAER';
    /**
     * @var string
     */
    protected const TEMPORARY = 'TEMPORAER';

    protected ilLogger $logger;
    protected ilRbacAdmin $rbac_admin;
    protected ilVedaELearningPlattformApiInterface $elearning_api;
    protected ilVedaCourseRepositoryInterface $crs_repo;
    protected ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory;
    /**
     * @var int[]
     */
    protected array $new_assignments;
    protected ilUDFClaimingPlugin $udf_claiming_plugin;
    protected ilVedaConnector $veda_connector;

    public function __construct(
        ilLogger $veda_logger,
        ilRbacAdmin $rbac_admin,
        ilVedaConnector $veda_connector,
        ilVedaCourseRepositoryInterface $crs_repo,
        ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory,
        ilUDFClaimingPlugin $udf_claiming_plugin
    ) {
        $this->logger = $veda_logger;
        $this->rbac_admin = $rbac_admin;
        $this->elearning_api = $veda_connector->getElearningPlattformApi();
        $this->crs_repo = $crs_repo;
        $this->repo_content_builder_factory = $repo_content_builder_factory;
        $this->new_assignments = [];
        $this->udf_claiming_plugin = $udf_claiming_plugin;
    }

    public function import() : void
    {
        $this->logger->debug('Reading "ELearning-Kurse" ...');
        $standard_courses = $this->crs_repo->lookupCoursesWithStatusAndType(
            ilVedaCourseStatus::SYNCHRONIZED,
            ilVedaCourseType::STANDARD
        );
        foreach ($standard_courses as $standard_cours) {
            $oid = $standard_cours->getOid();
            $obj_id = $standard_cours->getObjId();
            if (!$this->ensureCourseExists($obj_id)) {
                $this->logger->warning('Ignoring deleted course with id: ' . $obj_id);
                continue;
            }
            $this->synchronizeParticipants($oid, $obj_id);
        }
    }

    protected function synchronizeParticipants(string $oid, int $obj_id) : void
    {
        $tutors = $this->elearning_api->requestCourseTutors($oid);
        $supervisors = $this->elearning_api->requestCourseSupervisors($oid);
        $members = $this->elearning_api->requestCourseMembers($oid);
        if (
            is_null($tutors) ||
            is_null($supervisors) ||
            is_null($members)
        ) {
            $this->logger->warning('Api connection failed');
            return;
        }
        $participants = $this->initParticipants($obj_id);
        $course = $this->initCourse($obj_id);
        $this->removeDeprecatedMembers($participants, $course, $members);
        $this->addNewMembers($participants, $course, $members);
        $this->handleRemoveDeprecatedTutorsAndSupervisors($course, $participants, $tutors, $supervisors);
        $this->handleTutorAssignments($course, $participants, $tutors);
        $this->handleSupervisorAssignments($course, $participants, $supervisors);
    }

    protected function removeDeprecatedMembers(
        ilCourseParticipants $part,
        ilObjCourse $crs,
        ilVedaCourseMemberCollection $members
    ) : void {
        $this->logger->debug('Removing deprecated members');
        foreach ($part->getMembers() as $usr_id) {
            $usr_oid = \ilObject::_lookupImportId($usr_id);
            if (!$usr_oid) {
                $this->logger->debug('Keep member assignment for non synchonised account.');
            }
            if (!$members->containsMemberWithOID($usr_oid)) {
                $message = 'Deassigning user: ' . $usr_id . ' with oid ' . $usr_oid . ' from course: ' . $crs->getTitle();
                $this->logger->info($message);
                $this->rbac_admin->deassignUser(
                    $crs->getDefaultMemberRole(),
                    $usr_id
                );
                $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                    ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                    ->withMessage($message)
                    ->store();
            }
        }
    }

    protected function addNewMembers(
        ilCourseParticipants $participants,
        ilObjCourse $course,
        ilVedaCourseMemberCollectionInterface $members
    ) : void {
        $this->logger->debug('Adding new members');
        foreach ($members as $member) {
            $this->logger->debug('Validating ' . $member->getTeilnehmerId());
            $user_id = $this->getUserIdForImportId($member->getTeilnehmerId());
            $this->logger->debug('Found usr_id: ' . $user_id);
            if (!$user_id) {
                $this->logger->warning('Cannot find user id for import_id: ' . $member->getTeilnehmerId());
                continue;
            }
            if ($participants->isMember($user_id)) {
                $this->logger->debug('User with id: ' . $user_id . ' is already assigned to course: ' . $course->getTitle());
                continue;
            }
            if (ilVedaUtils::isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())) {
                $this->assignUserToRole(
                    $course->getDefaultMemberRole(),
                    $user_id,
                    $participants,
                    $course
                );
            } else {
                $this->logger->info('Ignoring user with access: ' . $member->getKursZugriffAb()->format('Y-m-d') . ', ' . $member->getKursZugriffBis()->format('Y-m-d'));
            }
        }
    }

    protected function initCourse(int $obj_id) : ilObjCourse
    {
        $refs = \ilObject::_getAllReferences($obj_id);
        $ref_id = end($refs);
        $course = ilObjectFactory::getInstanceByRefId($ref_id, false);
        if (!$course instanceof ilObjCourse) {
            $message = 'Invalid course id given: ' . $obj_id;
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
            throw new \InvalidArgumentException($message);
        }
        return $course;
    }

    protected function initParticipants(int $obj_id) : ilCourseParticipants
    {
        $refs = \ilObject::_getAllReferences($obj_id);
        $ref_id = end($refs);
        $participants = ilParticipants::getInstance($ref_id);
        if (!$participants instanceof ilCourseParticipants) {
            $message = 'Invalid participant id given: ' . $obj_id;
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
            throw new \InvalidArgumentException($message);
        }
        return $participants;
    }

    protected function ensureCourseExists(int $obj_id) : bool
    {
        $refs = \ilObject::_getAllReferences($obj_id);
        $ref_id = end($refs);
        try {
            $course = \ilObjectFactory::getInstanceByRefId($ref_id, false);
            if ($course instanceof ilObjCourse) {
                return true;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return false;
    }

    protected function assignUserToRole(
        int $role,
        int $user,
        \ilCourseParticipants $part,
        \ilObjCourse $course
    ) : void {
        $this->rbac_admin->assignUser($role, $user);
        if (!in_array($user, $this->new_assignments)) {
            $this->logger->debug('Adding new user sending mail notification...');
            $part->sendNotification($part->NOTIFY_ACCEPT_USER, $user);
            $favourites = new ilFavouritesManager();
            $favourites->add(
                $user,
                $course->getRefId()
            );
            $this->new_assignments[] = $user;
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                ->withMessage('Adding new user with role_id ' . $role . ' user_id ' . $user . ' to course with ref_id ' . $course->getRefId())
                ->store();
        }
    }

    protected function getUserIdForImportId(?string $oid) : int
    {
        return ilObject::_lookupObjIdByImportId($oid);
    }

    protected function handleRemoveDeprecatedTutorsAndSupervisors(
        ilObjCourse $course,
        ilCourseParticipants $participants,
        ilVedaCourseTutorsCollectionInterface $remote_tutors,
        ilVedaCourseSupervisorCollectionInterface $remote_supervisors
    ) : void
    {
        $udffields = $this->udf_claiming_plugin->getFields();
        if (
            is_null($remote_tutors) ||
            is_null($remote_supervisors)
        ) {
            $this->logger->warning('Reading assigned tutors failed. Aborting tutor update');
            return;
        }
        $valid_tutor_import_ids_with_udf_field_entry = [];
        $this->logger->info("Removing tutors with udf field entries that are not within a valid date range.");
        foreach ($participants->getTutors() as $tutor_id) {
            $tutor = \ilObjectFactory::getInstanceByObjId($tutor_id, false);
            if (!$tutor instanceof \ilObjUser) {
                $this->logger->warning('Found invalid tutor: ' . $tutor_id);
                continue;
            }
            $udf_data = $tutor->getUserDefinedData();
            $tutor_oid = '';
            if (isset($udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_TUTOR_ID]])) {
                $tutor_oid = $udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_TUTOR_ID]];
            }
            $companion_oid = '';
            if (isset($udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_COMPANION_ID]])) {
                $companion_oid = $udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_COMPANION_ID]];
            }
            $supervisor_oid = '';
            if (isset($udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_SUPERVISOR_ID]])) {
                $supervisor_oid = $udf_data['f_' . $udffields[\ilVedaUDFClaimingPlugin::FIELD_SUPERVISOR_ID]];
            }
            if (!$tutor_oid && !$companion_oid && !$supervisor_oid) {
                $this->logger->warning('Ignoring tutor without tutor_oid: ' . $tutor->getLogin());
                continue;
            }

            $found = false;
            foreach ($remote_tutors as $remote_tutor) {
                if (!$this->isValidDate($remote_tutor->getKursZugriffAb(), $remote_tutor->getKursZugriffBis())) {
                    $this->logger->debug(
                        'Ignoring tutor outside time frame: ' .
                        $remote_tutor->getDozentId()
                    );
                    continue;
                }
                if (ilVedaUtils::compareOidsEqual($remote_tutor->getDozentId(), $tutor_oid)) {
                    $found = true;
                    break;
                }
            }
            foreach ($remote_supervisors as $remote_supervisor) {
                if (!$this->isValidDate($remote_supervisor->getKursZugriffAb(), $remote_supervisor->getKursZugriffBis())) {
                    $this->logger->debug('Ignoring supervisor outside time frame: ' . $remote_supervisor->getLernbegleiterId());
                    continue;
                }
                if (ilVedaUtils::compareOidsEqual($remote_supervisor->getLernbegleiterId(), $supervisor_oid)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $message = 'Deassigning deprecated tutor from course: ' . $tutor->getLogin();
                $this->logger->info($message);
                $this->rbac_admin->deassignUser($course->getDefaultTutorRole(), $tutor_id);
                $participants->updateContact($tutor_id, false);
                $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                    ->withMessage($message)
                    ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                    ->store();
            }
            if ($found) {
                $valid_tutor_import_ids_with_udf_field_entry[] = $tutor->getImportId();
            }
        }

        $this->logger->debug('Removing tutors without remote');
        foreach ($participants->getTutors() as $user_id) {
            $import_id = \ilObject::_lookupImportId($user_id);
            if (!$import_id) {
                $this->logger->debug('Keep member assignment for non synchonised account.');
                continue;
            }
            if (in_array($import_id, $valid_tutor_import_ids_with_udf_field_entry)) {
                $this->logger->debug('Keep member assignment if udf field entry exists, independend of if a remote entry exists.');
                continue;
            }
            $this->logger->debug("Number of tutors: " . $remote_tutors->count());
            if (
                !$remote_tutors->containsTutorWithOID($import_id) &&
                !$remote_supervisors->containsSupervisorWithOID($import_id)
            ) {
                $message = 'Deassigning tutor: ' . $user_id . ' with oid ' . $import_id . ' from course: ' . $course->getTitle();
                $this->logger->info($message);
                $this->rbac_admin->deassignUser(
                    $course->getDefaultTutorRole(),
                    $user_id
                );
                $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                    ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                    ->withMessage($message)
                    ->store();
            }
        }
    }

    protected function handleTutorAssignments(
        ilObjCourse $course,
        ilCourseParticipants $participants,
        ilVedaCourseTutorsCollectionInterface $remote_tutors
    ) : void {
        if (
            is_null($remote_tutors)
        ) {
            $this->logger->warning('Reading assigned tutors failed. Aborting tutor update');
            return;
        }
        $this->logger->info("Assigning tutors to course " . $course->getTitle() . " by elearningbenutzeraccountid");
        foreach ($remote_tutors as $tutor) {
            $user_id = $this->getUserIdForImportId($tutor->getElearningbenutzeraccountId());
            if (!$user_id) {
                $this->logger->warning('Cannot find user id for import_id: ' . $tutor->getElearningbenutzeraccountId());
                continue;
            }
            if ($participants->isMember($user_id)) {
                $this->logger->debug('User with id: ' . $user_id . ' is already assigned to course: ' . $course->getTitle());
                continue;
            }
            if (ilVedaUtils::isValidDate($tutor->getKursZugriffAb(), $tutor->getKursZugriffBis())) {
                $this->assignUserToRole(
                    $course->getDefaultTutorRole(),
                    $user_id,
                    $participants,
                    $course
                );
            }
        }
        $this->logger->info("Assigning tutors to course  " . $course->getTitle() . " by dozent id in udf field.");
        foreach ($remote_tutors as $remote_tutor) {
            $tutor_oid = $remote_tutor->getDozentId();
            $this->logger->debug('Remote tutor oid is: ' . $tutor_oid);
            $this->logger->dump($this->udf_claiming_plugin->getUsersForTutorId($tutor_oid));
            if (!$this->isValidDate($remote_tutor->getKursZugriffAb(), $remote_tutor->getKursZugriffBis())) {
                $this->logger->info('Outside time frame: Ignoring tutor with id: ' . $tutor_oid);
                continue;
            }
            foreach ($this->udf_claiming_plugin->getUsersForTutorId($tutor_oid) as $uid) {
                $this->logger->info("Uid: " . $uid);
                if (!in_array($uid, $participants->getTutors())) {
                    $message = 'Assigning new course tutor with id: ' . $tutor_oid . ' ILIAS id: ' . $uid;
                    $this->logger->info($message);
                    $this->rbac_admin->assignUser($course->getDefaultTutorRole(), $uid);
                    $participants->updateContact($uid, true);
                    $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                        ->withMessage('Remote tutor oid is: ' . $tutor_oid)
                        ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                        ->store();
                }
            }
        }
    }

    protected function handleSupervisorAssignments(
        ilObjCourse $course,
        ilCourseParticipants $participants,
        ilVedaCourseSupervisorCollectionInterface $remote_supervisors
    ) : void {
        if (
            is_null($remote_supervisors)
        ) {
            $this->logger->warning('Reading assigned tutors failed. Aborting tutor update');
            return;
        }
        $this->logger->info("Assigning supervisors to course " . $course->getTitle() . "by elearningbenutzeraccountid.");
        foreach ($remote_supervisors as $supervisor) {
            $user_id = $this->getUserIdForImportId($supervisor->getElearningbenutzeraccountId());
            if (!$user_id) {
                $this->logger->warning('Cannot find user id for import_id: ' . $supervisor->getElearningbenutzeraccountId());
                continue;
            }
            if ($participants->isMember($user_id)) {
                $this->logger->debug('User with id: ' . $user_id . ' is already assigned to course: ' . $course->getTitle());
                continue;
            }
            if (ilVedaUtils::isValidDate($supervisor->getKursZugriffAb(), $supervisor->getKursZugriffBis())) {
                $this->assignUserToRole(
                    $course->getDefaultTutorRole(),
                    $user_id,
                    $participants,
                    $course
                );
            }
        }
        $this->logger->info("Assigning supervisors to course " . $course->getTitle() . " by supervisor id in udf field.");
        foreach ($remote_supervisors as $remote_supervisor) {
            $supervisor_id = $remote_supervisor->getLernbegleiterId();
            $this->logger->debug('Remote supervisor oid is: ' . $supervisor_id);
            $this->logger->dump($this->udf_claiming_plugin->getUsersForSupervisorId($supervisor_id));
            if (!$this->isValidDate($remote_supervisor->getKursZugriffAb(), $remote_supervisor->getKursZugriffBis())) {
                $this->logger->info('Outside time frame: Ignoring supervisor with id: ' . $supervisor_id);
                continue;
            }
            foreach ($this->udf_claiming_plugin->getUsersForSupervisorId($supervisor_id) as $uid) {
                if (!in_array($uid, $participants->getTutors())) {
                    $message = 'Assigning new course supervisor with id: ' . $supervisor_id . ' ILIAS id: ' . $uid;
                    $this->logger->info($message);
                    $this->rbac_admin->assignUser($course->getDefaultTutorRole(), $uid);
                    $participants->updateContact($uid, true);
                    $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                        ->withMessage($message)
                        ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                        ->store();
                }
            }
        }
    }

    protected function isValidDate(?DateTime $start, ?DateTime $end) : bool
    {
        if ($start == null && $end == null) {
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

        if ($end == null) {
            $ilstart = new \ilDate($start->format('Y-m-d'), IL_CAL_DATE);
            // check starting time <= now
            if (
                \ilDateTime::_before($ilstart, $now, IL_CAL_DAY) ||
                \ilDateTime::_equals($ilstart, $now, IL_CAL_DAY)
            ) {
                $this->logger->debug('Starting date is valid');
                return true;
            }
            $this->logger->debug('Starting date is invalid');
            return false;
        }

        $ilstart = new \ilDate($start->format('Y-m-d'), IL_CAL_DATE);
        $ilend = new \ilDate($end->format('Y-m-d'), IL_CAL_DATE);

        if (
            \ilDateTime::_within($now, $ilstart, $ilend, IL_CAL_DAY) ||
            \ilDateTime::_equals($now, $ilend, \ilDateTime::DAY)
        ) {
            return true;
        }
        return false;
    }
}
