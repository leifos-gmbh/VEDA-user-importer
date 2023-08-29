<?php

use OpenApi\Client\Model\AusbildungszugTeilnehmer;

class ilVedaMemberImportAdapter
{
    /**
     * @var string
     */
    protected const REGULAR = 'REGULAER';
    /**
     * @var string
     */
    protected const TEMPORARY = 'TEMPORAER';

    protected ?ilLogger $logger;
    protected ilRbacAdmin $rbac_admin;
    protected ilRbacReview $rbac_review;
    protected ilVedaConnector $veda_connector;
    protected ilUDFClaimingPlugin $udf_claiming_plugin;
    protected ilVedaMDClaimingPluginDBManagerInterface $md_db_manager;
    protected ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory;

    public function __construct(
        ilLogger $veda_logger,
        ilRbacAdmin $rbac_admin,
        ilRbacReview $rbac_review,
        ilVedaConnector $veda_connector,
        ilUDFClaimingPlugin $udf_claiming_plugin,
        ilVedaMDClaimingPluginDBManagerInterface $md_db_manager,
        ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory
    ) {
        $this->rbac_admin = $rbac_admin;
        $this->rbac_review = $rbac_review;
        $this->logger = $veda_logger;
        $this->veda_connector = $veda_connector;
        $this->udf_claiming_plugin = $udf_claiming_plugin;
        $this->md_db_manager = $md_db_manager;
        $this->repo_content_builder_factory = $repo_content_builder_factory;
    }

    public function import() : void
    {
        $this->logger->debug('Reading "Ausbildungszüge" ...');
        foreach ($this->md_db_manager->findTrainingCourseTrains() as $oid) {
            $this->importTrainingCourseTrain($oid);
        }
    }

    public function handleTrackingEvent(int $obj_id, int $usr_id, int $status) : void
    {
        if ($status != ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            $this->logger->debug('Ignoring non completed event.');
            return;
        }
        $usr_oid = \ilObjUser::_lookupImportId($usr_id);
        if (!$usr_oid) {
            $this->logger->debug('Not imported user.');
            return;
        }
        // additional check in user status table
        $us = $this->repo_content_builder_factory->getVedaUserBuilder()->buildUser()->withOID($usr_oid)->get();
        if ($us->getCreationStatus() != ilVedaUserStatus::SYNCHRONIZED) {
            $this->logger->info('Ignoring not synchronized user account: ' . $usr_oid);
            return;
        }
        if (\ilObject::_lookupType($obj_id) != 'exc') {
            $this->logger->debug('Ignoring non session event');
            return;
        }

        $refs = ilObject::_getAllReferences($obj_id);
        $ref = end($refs);

        $segment_id = $this->md_db_manager->findTrainSegmentId($ref);

        if (!$segment_id) {
            $this->logger->debug('Not ausbildungszugabschnitt');
            return;
        }
        $this->sendExerciseSuccessInformation($obj_id, $usr_id, $usr_oid, $segment_id);
    }

    /**
     * @throws ilDatabaseException
     */
    protected function sendExerciseSuccessInformation(
        int $obj_id,
        int $usr_id,
        string $usr_oid,
        string $segment_id
    ) : void {
        // find parent courses
        $exercise = \ilObjectFactory::getInstanceByObjId($obj_id, false);
        if (!$exercise instanceof \ilObjExercise) {
            $this->logger->warning('Cannot create exercise instance');
            return;
        }

        // find ref_ids for exercise
        $refs = \ilObject::_getAllReferences($exercise->getId());

        $is_practical_training = false;
        $is_self_learning = false;
        $submission_date_str = '';
        foreach ($refs as $tmp => $ref_id) {
            $segment_id = $this->md_db_manager->findTrainSegmentId($ref_id);
            $this->logger->debug('Current ref_id: ' . $ref_id . ' has segment_id: ' . $segment_id);
            $segment_info = $this->repo_content_builder_factory->getVedaSegmentBuilder()->buildSegment()
                ->withOID($segment_id)
                ->get();
            if ($segment_info->isPracticalTraining()) {
                $this->logger->info('Exercise of type "practical training"');
                $is_practical_training = true;
            } elseif ($segment_info->isSelfLearning()) {
                $this->logger->info('Exercise of type "self learning"');
                $is_self_learning = true;
            } else {
                $this->logger->info('No practical training type, no self learning type');
                break;
            }
            $assignments = \ilExAssignment::getInstancesByExercise($exercise->getId());
            foreach ($assignments as $assignment) {
                $submission = new \ilExSubmission($assignment, $usr_id);
                $submission_date_str = $submission->getLastSubmission();
                $this->logger->notice('Last submission is: ' . $submission_date_str);
            }
            break;
        }

        if ($is_practical_training && $submission_date_str) {
            $submission_date = new DateTime($submission_date_str);
            $education_train_segment_api = $this->veda_connector->getEducationTrainSegmentApi();
            if (
                !$education_train_segment_api->sendExerciseSubmissionDate($segment_id, $usr_oid, $submission_date) ||
                !$education_train_segment_api->sendExerciseSubmissionConfirmed($segment_id, $usr_oid, new \DateTime()) ||
                !$education_train_segment_api->sendExerciseSuccess($segment_id, $usr_oid, new \DateTime())
            ) {
                $this->logger->error('Send exercise success failed');
            }
        } elseif ($is_practical_training) {
            $ref_id = (count($refs) > 0) ? ('' . $refs[count($refs) - 1]) : 'NOT FOUND';
            $this->logger->notice('Did not send exercise success messages for user without submission. ');
            $this->logger->notice('User id: ' . $usr_id);
            $this->logger->notice('Exercise ref_id: ' . $ref_id);
        }
        if ($is_self_learning) {
            $education_train_segment_api = $this->veda_connector->getEducationTrainSegmentApi();
            if (!$education_train_segment_api->sendExerciseSuccess($segment_id, $usr_oid, new \DateTime())) {
                $this->logger->error('Send exercise success for type "self training" failed');
            }
        }
    }

    /**
     * @throws ilVedaMemberImportException
     */
    protected function importTrainingCourseTrain(?string $oid) : void
    {
        // read member info
        $members = $this->veda_connector->getEducationTrainApi()->requestMembers($oid);

        $course_ref_id = $this->md_db_manager->findTrainingCourseTrain($oid);
        $course = \ilObjectFactory::getInstanceByRefId($course_ref_id);
        if (!$course instanceof \ilObjCourse) {
            $message = 'Cannot find course for oid: ' . $oid;
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
            throw new ilVedaMemberImportException($message);
        }
        $participants = \ilParticipants::getInstance($course_ref_id);
        if (!$participants instanceof \ilCourseParticipants) {
            $message = 'Cannot find course participants for oid: ' . $oid;
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
            throw new ilVedaMemberImportException($message);
        }

        $this->logger->debug('Handling course: ' . $course->getTitle());

        $veda_crs = $this->repo_content_builder_factory->getVedaCourseBuilder()->buildCourse()
            ->withOID($oid)
            ->get();

        $currently_assigned = $participants->getParticipants();

        $this->removeInvalidRegularMembers($course, $participants, $members, $veda_crs, $currently_assigned);
        $this->removeInvalidPermanentSwitchMembers($course, $participants, $members, $veda_crs, $currently_assigned);
        $this->removeInvalidTemporarySwitchMembers($course, $participants, $members, $veda_crs, $currently_assigned);

        $this->addRegularMembers($course, $participants, $members, $veda_crs, $currently_assigned);
        $this->addPermanentSwitchMembers($course, $participants, $members, $veda_crs, $currently_assigned);
        $this->addTemporarySwitchMembers($course, $participants, $members, $veda_crs, $currently_assigned);

        $this->handleTutorAssignments($course, $participants, $oid);
    }

    protected function handleTutorAssignments(
        \ilObjCourse $course,
        \ilCourseParticipants $participants,
        ?string $oid
    ) : bool {
        $udffields = $this->udf_claiming_plugin->getFields();
        $education_train_api = $this->veda_connector->getEducationTrainApi();
        $remote_tutors = $education_train_api->requestTutors($oid);
        $remote_companions = $education_train_api->requestCompanions($oid);
        $remote_supervisors = $education_train_api->requestSupervisors($oid);

        if (
            is_null($remote_tutors) ||
            is_null($remote_companions) ||
            is_null($remote_supervisors)
        ) {
            $this->logger->warning('Reading assigned tutors failed. Aborting tutor update');
            return false;
        }

        // deassign deprecated tutors
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
                $this->logger->debug('Ignoring tutor without tutor_oid: ' . $tutor->getLogin());
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
            foreach ($remote_companions as $remote_companion) {
                if (!$this->isValidDate($remote_companion->getZustaendigAb(), $remote_companion->getZustaendigBis())) {
                    $this->logger->debug('Ignoring companion outside time frame: ' . $remote_companion->getLernbegleiterId());
                    continue;
                }
                if (ilVedaUtils::compareOidsEqual($remote_companion->getLernbegleiterId(), $companion_oid)) {
                    $found = true;
                    break;
                }
            }
            foreach ($remote_supervisors as $remote_supervisor) {
                if (!$this->isValidDate($remote_supervisor->getKursZugriffAb(), $remote_supervisor->getKursZugriffBis())) {
                    $this->logger->debug('Ignoring supervisor outside time frame: ' . $remote_supervisor->getAufsichtspersonId());
                    continue;
                }
                if (ilVedaUtils::compareOidsEqual($remote_supervisor->getAufsichtspersonId(), $supervisor_oid)) {
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
        }
        // assign missing tutors
        foreach ($remote_tutors as $remote_tutor) {
            $tutor_oid = $remote_tutor->getDozentId();
            $this->logger->debug('Remote tutor oid is: ' . $tutor_oid);
            $this->logger->dump($this->udf_claiming_plugin->getUsersForTutorId($tutor_oid));

            foreach ($this->udf_claiming_plugin->getUsersForTutorId($tutor_oid) as $uid) {
                if (!in_array($uid, $participants->getTutors())) {
                    $this->rbac_admin->assignUser($course->getDefaultTutorRole(), $uid);
                    $participants->updateContact($uid, true);
                    $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                        ->withMessage('Remote tutor oid is: ' . $tutor_oid)
                        ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                        ->store();
                }
            }
        }
        // assign companions
        foreach ($remote_companions as $remote_companion) {
            $companion_id = $remote_companion->getLernbegleiterId();
            $this->logger->debug('Remote companion oid is: ' . $companion_id);
            $this->logger->dump($this->udf_claiming_plugin->getUsersForCompanionId($companion_id));

            if (!$this->isValidDate($remote_companion->getZustaendigAb(), $remote_companion->getZustaendigBis())) {
                $this->logger->info('Outside time frame: Ignoring companion with id: ' . $companion_id);
                continue;
            }
            foreach ($this->udf_claiming_plugin->getUsersForCompanionId($companion_id) as $uid) {
                if (!in_array($uid, $participants->getTutors())) {
                    $message = 'Assigning new course tutor with id: ' . $companion_id . ' ILIAS id: ' . $uid;
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
        foreach ($remote_supervisors as $remote_supervisor) {
            $supervisor_id = $remote_supervisor->getAufsichtspersonId();
            $this->logger->debug('Remote supervisor oid is: ' . $supervisor_id);
            $this->logger->dump($this->udf_claiming_plugin->getUsersForSupervisorId($supervisor_id));

            if (!$this->isValidDate($remote_supervisor->getKursZugriffAb(), $remote_supervisor->getKursZugriffBis())) {
                $this->logger->info('Outside time frame: Ignoring supervisor with id: ' . $supervisor_id);
                continue;
            }
            foreach ($this->udf_claiming_plugin->getUsersForSupervisorId($supervisor_id) as $uid) {
                if (!in_array($uid, $participants->getTutors())) {
                    $message = 'Assigning new course tutor with id: ' . $supervisor_id . ' ILIAS id: ' . $uid;
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
        return true;
    }

    /**
     * @param int[] $assigned
     */
    protected function removeInvalidRegularMembers(
        \ilObjCourse $course,
        \ilCourseParticipants $part,
        ilVedaEducationTrainMemberCollectionInterface $members,
        ilVedaCourse $status,
        array $assigned
    ) : void {
        foreach ($this->rbac_review->assignedUsers($course->getDefaultMemberRole()) as $participant) {
            $oid = \ilObjUser::_lookupImportId($participant);
            if (!$oid) {
                continue;
            }

            $found = false;
            foreach ($members as $member) {
                if (strtolower($member->getTeilnehmerId()) != strtolower($oid)) {
                    continue;
                }
                if (
                    $member->getMitgliedschaftsart() == self::REGULAR &&
                    !$member->getWechsel() &&
                    $this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())
                ) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $message = 'Deassigning user: ' . $participant . ' with oid ' . $oid . ' from course: ' . $course->getTitle();
                $this->logger->info($message);
                $this->rbac_admin->deassignUser(
                    $course->getDefaultMemberRole(),
                    $participant
                );
                $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                    ->withMessage($message)
                    ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                    ->store();
            }
        }
    }

    /**
     * @param int[] $assigned
     */
    protected function removeInvalidPermanentSwitchMembers(
        \ilObjCourse $course,
        \ilCourseParticipants $part,
        ilVedaEducationTrainMemberCollectionInterface $members,
        ilVedaCourse $status,
        array $assigned
    ) : void {
        foreach ($this->rbac_review->assignedUsers($status->getPermanentSwitchRole()) as $participant) {
            $oid = \ilObjUser::_lookupImportId($participant);
            if (!$oid) {
                $this->logger->debug('Ignoring non imported user.');
                continue;
            }

            $found = false;
            foreach ($members as $member) {
                if (strtolower($member->getTeilnehmerId()) != strtolower($oid)) {
                    continue;
                }
                if (
                    $member->getMitgliedschaftsart() == self::REGULAR &&
                    $member->getWechsel() &&
                    $this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())
                ) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $message = 'Deassigning user: ' . $participant . ' with oid ' . $oid . ' from course: ' . $course->getTitle();
                $this->logger->info($message);
                $this->rbac_admin->deassignUser(
                    $status->getPermanentSwitchRole(),
                    $participant
                );
                $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                    ->withMessage($message)
                    ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                    ->store();
            }
        }
    }

    /**
     * @param int[] $assigned
     */
    protected function removeInvalidTemporarySwitchMembers(
        \ilObjCourse $course,
        \ilCourseParticipants $part,
        ilVedaEducationTrainMemberCollectionInterface $members,
        ilVedaCourse $status,
        array $assigned
    ) {
        foreach ($this->rbac_review->assignedUsers($status->getTemporarySwitchRole()) as $participant) {
            $oid = \ilObjUser::_lookupImportId($participant);
            if (!$oid) {
                continue;
            }

            $found = false;
            foreach ($members as $member) {
                if (strtolower($member->getTeilnehmerId()) != strtolower($oid)) {
                    continue;
                }
                if ($member->getMitgliedschaftsart() == self::REGULAR && $member->getWechsel()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $message = 'Deassigning user: ' . $participant . ' with oid ' . $oid . ' from course: ' . $course->getTitle();
                $this->logger->info($message);
                $this->rbac_admin->deassignUser(
                    $status->getTemporarySwitchRole(),
                    $participant
                );
                $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                    ->withMessage($message)
                    ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                    ->store();
            }
        }
    }

    /**
     * @param int[] $assigned
     */
    protected function addRegularMembers(
        \ilObjCourse $course,
        \ilCourseParticipants $part,
        ilVedaEducationTrainMemberCollectionInterface $members,
        ilVedaCourse $status,
        array $assigned
    ) : void {
        foreach ($members as $member) {
            if ($member->getMitgliedschaftsart() != self::REGULAR) {
                $this->logger->debug('Ignoring TEMPORAER member.');
                continue;
            }
            if ($member->getWechsel()) {
                $this->logger->debug('Ignoring switch membership.');
                continue;
            }
            if (!$this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())) {
                $this->logger->debug('Ignoring deprecated assignment.');
                continue;
            }

            $uid = $this->getUserIdForImportId($member->getTeilnehmerId());

            if ($uid) {
                $this->logger->info('Assigning user: ' . $uid . ' with oid ' . $member->getTeilnehmerId() . ' to course: ' . $course->getTitle());
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
     * @param int[] $assigned
     */
    protected function addPermanentSwitchMembers(
        \ilObjCourse $course,
        \ilCourseParticipants $part,
        ilVedaEducationTrainMemberCollectionInterface $members,
        ilVedaCourse $status,
        array $assigned
    ) : void {
        foreach ($members as $member) {
            if ($member->getMitgliedschaftsart() != self::REGULAR) {
                $this->logger->debug('Ignoring TEMPORAER member.');
                continue;
            }
            if (!$member->getWechsel()) {
                $this->logger->debug('Ignoring regular membership.');
                continue;
            }
            if (!$this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())) {
                $this->logger->debug('Ignoring deprecated assignment.');
                continue;
            }

            $uid = $this->getUserIdForImportId($member->getTeilnehmerId());

            if ($uid) {
                $this->logger->info('Assigning user: ' . $uid . ' with oid ' . $member->getTeilnehmerId() . ' to course: ' . $course->getTitle());
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
     * @param int[] $assigned
     */
    protected function addTemporarySwitchMembers(
        \ilObjCourse $course,
        \ilCourseParticipants $part,
        ilVedaEducationTrainMemberCollectionInterface $members,
        ilVedaCourse $status,
        array $assigned
    ) : void {
        foreach ($members as $member) {
            if ($member->getMitgliedschaftsart() == self::REGULAR) {
                $this->logger->debug('Ignoring permanent member.');
                continue;
            }
            if (!$this->isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())) {
                $this->logger->debug('Ignoring deprecated assignment.');
                continue;
            }

            $uid = $this->getUserIdForImportId($member->getTeilnehmerId());

            if ($uid) {
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
     * @param int[] $assigned
     */
    protected function assignUserToRole(
        int $role,
        int $user,
        array &$assigned,
        \ilCourseParticipants $part,
        \ilObjCourse $course
    ) : void {
        $this->rbac_admin->assignUser($role, $user);
        if (!in_array($user, $assigned)) {
            $this->logger->debug('Adding new user sending mail notification...');
            $part->sendNotification($part->NOTIFY_ACCEPT_USER, $user);
            $favourites = new ilFavouritesManager();
            $favourites->add(
                $user,
                $course->getRefId()
            );
            $message = 'Assigning user: ' . $user . ' with role id ' . $role . ' to course: ' . $course->getTitle();
            $assigned[] = $user;
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                ->store();
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

    protected function getUserIdForImportId(?string $oid) : int
    {
        return ilObject::_lookupObjIdByImportId($oid);
    }
}
