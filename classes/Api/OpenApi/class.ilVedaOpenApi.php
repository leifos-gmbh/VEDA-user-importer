<?php

use OpenAPI\Client\Configuration;

class ilVedaOpenApi implements ilVedaApiInterface
{
    /**
     * @var string
     */
    protected const REMOTE_SESSION_TYPE = 'Präsenz';
    /**
     * @var string
     */
    protected const REMOTE_EXERCISE_TYPE = 'Selbstlernen';

    protected ilVedaConnector $veda_connector;
    protected ilVedaCourseImportAdapter $sifa_course_import_adapter;
    protected ilVedaCourseStandardImportAdapter $standard_course_import_adapter;
    protected ilVedaMemberImportAdapter $sifa_member_import_adapter;
    protected ilVedaMemberStandardImportAdapter $standard_member_import_adapter;
    protected ilVedaUserImportAdapter $user_import_adapter;
    protected ilVedaUserRepositoryInterface $user_repo;
    protected ilVedaCourseRepositoryInterface $crs_repo;
    protected ilVedaMDClaimingPluginDBManagerInterface $md_db_manager;
    protected ilLogger $veda_logger;
    protected ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory;

    public function __construct()
    {
        global $DIC;
        $il_db = $DIC->database();
        $object_definition = $DIC['objDefinition'];
        $rbac_admin = $DIC->rbac()->admin();
        $rbac_review = $DIC->rbac()->review();
        $user = $DIC->user();

        $repo_factory = new ilVedaRepositoryFactory();
        $veda_settings = new ilVedaConnectorSettings();

        $this->veda_logger = $DIC->logger()->vedaimp();
        $this->crs_repo = $repo_factory->getCourseRepository();
        $this->md_db_manager = $repo_factory->getMDClaimingPluginRepository();
        $this->user_repo = $repo_factory->getUserRepository();
        $this->repo_content_builder_factory = new ilVedaRepositoryContentBuilderFactory(
            $repo_factory,
            $this->veda_logger
        );
        $sgmt_builder_factory = new ilVedaSegmentBuilderFactory($repo_factory->getSegmentRepository(), $this->veda_logger);

        $this->veda_connector = new ilVedaConnector(
            $this->veda_logger,
            $veda_settings,
            $this->repo_content_builder_factory
        );
        $this->sifa_course_import_adapter = new ilVedaCourseImportAdapter(
            $user,
            $object_definition,
            $rbac_admin,
            $rbac_review,
            $this->veda_logger,
            $this->veda_connector,
            $this->md_db_manager,
            $veda_settings,
            $this->repo_content_builder_factory
        );
        $this->standard_course_import_adapter = new ilVedaCourseStandardImportAdapter(
            $user,
            $object_definition,
            $this->veda_logger,
            $veda_settings,
            $this->veda_connector,
            $this->repo_content_builder_factory
        );
        $this->sifa_member_import_adapter = new ilVedaMemberImportAdapter(
            $this->veda_logger,
            $rbac_admin,
            $rbac_review,
            $this->veda_connector,
            ilVedaConnectorPlugin::getInstance()->getUDFClaimingPlugin(),
            $this->md_db_manager,
            $this->repo_content_builder_factory
        );
        $this->standard_member_import_adapter = new ilVedaMemberStandardImportAdapter(
            $this->veda_logger,
            $rbac_admin,
            $this->veda_connector,
            $this->crs_repo,
            $this->repo_content_builder_factory,
            ilVedaConnectorPlugin::getInstance()->getUDFClaimingPlugin()
        );
        $this->user_import_adapter = new ilVedaUserImportAdapter(
            $this->veda_logger,
            $veda_settings,
            $this->user_repo,
            $this->veda_connector,
            $this->repo_content_builder_factory
        );
    }

    public function handleAfterCloningDependenciesSIFAEvent(int $source_id, int $target_id, int $copy_id) : void
    {
        $this->sifa_course_import_adapter->handleAfterCloningDependenciesEvent(
            $source_id,
            $target_id,
            $copy_id
        );
    }

    public function handleAfterCloningDependenciesStandardEvent(int $source_id, int $target_id, int $copy_id) : void
    {
        $this->standard_course_import_adapter->handleAfterCloningDependenciesEvent(
            $source_id,
            $target_id,
            $copy_id
        );
    }

    public function handleAfterCloningSIFAEvent(int $a_source_id, int $a_target_id, int $a_copy_id) : void
    {
        $this->sifa_course_import_adapter->handleAfterCloningEvent(
            $a_source_id,
            $a_target_id,
            $a_copy_id
        );
    }

    public function handleAfterCloningStandardEvent(int $a_source_id, int $a_target_id, int $a_copy_id) : void
    {
        $this->standard_course_import_adapter->handleAfterCloningEvent(
            $a_source_id,
            $a_target_id,
            $a_copy_id
        );
    }

    protected function handleTrackingEventDokumentSuccess(int $obj_id, int $usr_id, int $status)
    {
        $this->veda_logger->debug(
            'Handling tracking event to document success (obj_id, user_id, status): ('
            . $obj_id . ', '
            . $usr_id . ', '
            . $status . ')'
        );
        $crs_oid = null;
        $usr_oid = null;

        if (
            $status !== ilLPStatus::LP_STATUS_COMPLETED_NUM &&
            $status !== ilLPStatus::LP_STATUS_FAILED_NUM
        ) {
            $this->veda_logger->debug('Ignoring every learning progress status except: failed, completed');
            return;
        }
        if (!ilObjCourse::_exists($obj_id)) {
            $this->veda_logger->debug('Course with id does not exist: ' . $obj_id);
            return;
        }
        if (!ilObjUser::_exists($usr_id)) {
            $this->veda_logger->debug('User with id does not exist: ' . $usr_id);
            return;
        }
        if (
            is_null(($usr_oid = ilObjUser::_lookupImportId($usr_id))) ||
            $usr_oid == ''
        ) {
            $this->veda_logger->debug('User oid is null or empty.');
            return;
        }
        if (
            is_null(($crs_oid = ilObjCourse::_lookupImportId($obj_id))) ||
            $crs_oid == ''
        ) {
            $this->veda_logger->debug('Course oid is null or empty.');
            return;
        }

        $veda_usr = $this->user_repo->lookupUserByOID($usr_oid);
        $veda_crs = $this->crs_repo->lookupCourseByOID($crs_oid);

        if (is_null($veda_usr)) {
            $this->veda_logger->debug('User with oid does not exist: ' . $crs_oid);
            return;
        }
        if (is_null($veda_crs)) {
            $this->veda_logger->debug('Course with oid does not exist: ' . $crs_oid);
            return;
        }
        if (!$veda_crs->getDocumentSuccess()) {
            $this->veda_logger->debug('Document success is not enabled for course with oid:' . $crs_oid);
            return;
        }

        $elearning_api = $this->veda_connector->getElearningPlattformApi();
        if ($status === ilLPStatus::LP_STATUS_FAILED_NUM) {
            $this->veda_logger->debug('Send usr: ' . $usr_oid . ' failed crs: ' . $crs_oid);
            $elearning_api->sendCourseFailed($crs_oid, $usr_oid);
            return;
        }

        if ($status === ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            $this->veda_logger->debug('Send usr: ' . $usr_oid . ' passed crs: ' . $crs_oid);
            $elearning_api->sendCoursePassed($crs_oid, $usr_oid);
        }
    }

    protected function handleTrackingEventStartCourseWork(int $obj_id, int $usr_id, int $status)
    {
        $this->veda_logger->debug(
            'Start handling participant started working on course (obj_id, user_id, status): ('
            . $obj_id . ', '
            . $usr_id . ', '
            . $status . ')'
        );

        if ($status != ilLPStatus::LP_STATUS_IN_PROGRESS_NUM) {
            $this->veda_logger->debug('Ignoring every learning progress status except: in progress');
            return;
        }

        $veda_crs = $this->crs_repo->lookupCourseByID($obj_id);
        $veda_usr = $this->user_repo->lookupUserByID($usr_id);

        if (is_null($veda_crs) || is_null($veda_usr)) {
            $this->veda_logger->debug('handleParticipantAssignedToCourse, null course or user');
            return;
        }
        if (is_null($veda_crs->getOid()) || is_null($veda_usr->getOid())) {
            $this->veda_logger->debug('handleParticipantAssignedToCourse, null course_oid or user_oid');
            return;
        }
        if (!$veda_crs->getDocumentSuccess()) {
            $this->veda_logger->debug('Ignore course without document success flag');
            return;
        }

        $this->veda_logger->debug('Send usr:' . $veda_usr->getOid() . ' started working on crs:' . $veda_crs->getOid());
        $this->veda_connector->getElearningPlattformApi()->sendParticipantStartedCourseWork(
            $veda_crs->getOid(),
            $veda_usr->getOid()
        );
    }

    public function handleTrackingEvent(int $obj_id, int $usr_id, int $status) : void
    {
        $this->handleTrackingEventStartCourseWork(
            $obj_id,
            $usr_id,
            $status
        );

        $this->handleTrackingEventDokumentSuccess(
            $obj_id,
            $usr_id,
            $status
        );

        $this->sifa_member_import_adapter->handleTrackingEvent(
            $obj_id,
            $usr_id,
            $status
        );
    }

    public function handlePasswordChanged(int $usr_id) : void
    {
        $import_id = ilObjUser::_lookupImportId($usr_id);

        if (!$import_id) {
            $this->veda_logger->debug('No import id for user ' . $usr_id);
            return;
        }

        $veda_user = $this->repo_content_builder_factory->getVedaUserBuilder()->buildUser()
            ->withOID($import_id)
            ->get();

        if (
            $veda_user->isImportFailure() ||
            $veda_user->getPasswordStatus() != ilVedaUserStatus::PENDING
        ) {
            $this->veda_logger->debug('No password notification required.');
        }

        $this->veda_connector->getElearningPlattformApi()->sendFirstLoginSuccess($veda_user->getOid());

        $this->repo_content_builder_factory->getVedaUserBuilder()->buildUser()
            ->withOID($import_id)
            ->withPasswordStatus(ilVedaUserStatus::SYNCHRONIZED)
            ->store();
    }

    public function deleteDeprecatedILIASUsers() : void
    {
        $elearning_api = $this->veda_connector->getElearningPlattformApi();
        foreach ($this->user_repo->lookupAllUsers() as $user) {
            $found_remote = false;
            $participants = $elearning_api->requestParticipants();
            if (is_null($participants)) {
                continue;
            }
            foreach ($elearning_api->requestParticipants() as $participant) {
                if (ilVedaUtils::compareOidsEqual($user->getOid(), $participant->getTeilnehmer()->getOid())) {
                    $found_remote = true;
                }
            }
            if (!$found_remote) {
                $this->user_repo->deleteUserByOID($user->getOid());
            }
        }
    }

    public function handleCloningFailed() : void
    {
        $failed = $this->crs_repo->lookupAllCourses()->getAsynchronusCourses();
        foreach ($failed as $fail) {
            $oid = $fail->getOid();
            $message = '';
            $this->veda_logger->notice('Handling failed clone event for oid: ' . $fail->getOid());
            if (
                $fail->getType() == ilVedaCourseType::SIFA
            ) {
                $this->veda_connector->getEducationTrainApi()->sendCourseCreationFailed($oid);
                $message = 'SIFA course cloning failed, course oid: ' . $fail->getOid();
            } elseif (
                $fail->getType() == ilVedaCourseType::STANDARD
            ) {
                $this->veda_connector->getElearningPlattformApi()->sendCourseCreationFailed(
                    $oid,
                    'Synchronisierung des ELearning-Kurses fehlgeschlagen.'
                );
                $message = 'Standard course cloning failed, course oid: ' . $fail->getOid();
            } else {
                $message = 'Unknown course cloning failed, course oid: ' . $fail->getOid();
                $this->veda_logger->error('Unknown type given for oid ' . $fail->getOid());
            }
            $this->repo_content_builder_factory->getVedaCourseBuilder()->buildCourse()
                ->withOID($fail->getOid())
                ->withModified(time())
                ->withStatusCreated(ilVedaCourseStatus::FAILED)
                ->store();

            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
        }
    }

    /**
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilObjectNotFoundException
     * @throws ilSaxParserException
     * @throws ilVedaUserImporterException
     */
    public function importILIASUsersStandard() : void
    {
        $participants = $this->veda_connector->getElearningPlattformApi()->requestParticipants();
        if (is_null($participants)) {
            return;
        }
        $this->user_import_adapter->import($participants, ilVedaUserImportAdapter::IMPORT_MODE_STANDARD);
    }

    /**
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilObjectNotFoundException
     * @throws ilSaxParserException
     * @throws ilVedaUserImporterException
     */
    public function importILIASUsersSIFA() : void
    {
        $participants = $this->veda_connector->getElearningPlattformApi()->requestParticipants();
        if (is_null($participants)) {
            return;
        }
        $this->user_import_adapter->import($participants, ilVedaUserImportAdapter::IMPORT_MODE_SIFA);
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     * @throws ilVedaCourseImporterException
     * @throws ilSaxParserException
     */
    public function importStandardCourses() : void
    {
        $this->standard_course_import_adapter->import();
    }

    public function importSIFACourses() : void
    {
        $this->sifa_course_import_adapter->import();
    }

    public function importSIFAMembers() : void
    {
        $this->sifa_member_import_adapter->import();
    }

    public function importStandardMembers() : void
    {
        $this->standard_member_import_adapter->import();
    }

    public function isTrainingCourseValid($course_oid) : bool
    {
        $training_course = $this->veda_connector->getTrainingCourseApi()->getCourse($course_oid);
        if (!is_null($training_course)) {
            $this->veda_logger->dump($training_course, ilLogLevel::DEBUG);
        }
        return !is_null($training_course);
    }

    public function validateLocalSessions(array $sessions, string $course_oid) : array
    {
        $missing = [];
        $training_course = $this->veda_connector->getTrainingCourseApi()->getCourse($course_oid);

        if (is_null($training_course)) {
            return $missing;
        }

        foreach ($sessions as $index => $node) {
            if (!$node['vedaid']) {
                continue;
            }
            $local_id = $node['vedaid'];
            $found_remote = false;
            foreach ($training_course->getAusbildungsgangabschnitte() as $segment) {
                if (!$segment->getAbbildungAufELearningPlattform()) {
                    $this->veda_logger->debug('Ignoring of type: !AbbildungAufELearningPlattform');
                    continue;
                }

                if ($segment->getAusbildungsgangabschnittsart() != self::REMOTE_SESSION_TYPE) {
                    $this->veda_logger->debug('Ignoring type: ' . $segment->getAusbildungsgangabschnittsart());
                    continue;
                }

                $remote_id = $segment->getOid();
                if (ilVedaUtils::compareOidsEqual($local_id, $remote_id)) {
                    $found_remote = true;
                    break;
                }
            }
            if (!$found_remote) {
                $missing[] = $node;
            }
        }
        return $missing;
    }

    public function validateRemoteSessions(array $sessions, string $course_oid) : array
    {
        $missing = [];
        $training_course = $this->veda_connector->getTrainingCourseApi()->getCourse($course_oid);

        if (is_null($training_course)) {
            return $missing;
        }

        foreach ($training_course->getAusbildungsgangabschnitte() as $segment) {
            if (!$segment->getAbbildungAufELearningPlattform()) {
                $this->veda_logger->debug('Ignoring of type: !AbbildungAufELearningPlattform');
                continue;
            }

            if ($segment->getAusbildungsgangabschnittsart() != self::REMOTE_SESSION_TYPE) {
                $this->veda_logger->debug('Ignoring segment of type: ' . $segment->getAusbildungsgangabschnittsart());
                continue;
            }
            $found_local = false;
            foreach ($sessions as $index => $node) {
                $local_id = $node['vedaid'];
                $remote_id = $segment->getOid();
                if (ilVedaUtils::compareOidsEqual($local_id, $remote_id)) {
                    $found_local = true;
                    break;
                }
            }
            if (!$found_local) {
                $missing[$segment->getOid()] = $segment->getBezeichnung();
            }
        }
        return $missing;
    }

    public function validateLocalExercises(array $exercises, string $course_oid) : array
    {
        $missing = [];
        $training_course = $this->veda_connector->getTrainingCourseApi()->getCourse($course_oid);

        if (is_null($training_course)) {
            return $missing;
        }

        foreach ($exercises as $index => $node) {
            if (!$node['vedaid']) {
                continue;
            }
            $local_id = $node['vedaid'];
            $found_remote = false;
            foreach ($training_course->getAusbildungsgangabschnitte() as $segment) {
                if (!$segment->getAbbildungAufELearningPlattform()) {
                    $this->veda_logger->debug('Ignoring of type: !AbbildungAufELearningPlattform');
                    continue;
                }
                $remote_id = $segment->getOid();
                if (ilVedaUtils::compareOidsEqual($local_id, $remote_id)) {
                    $found_remote = true;
                    break;
                }
            }
            if (!$found_remote) {
                $missing[] = $node;
            }
        }
        return $missing;
    }

    public function validateRemoteExercises(array $exercises, string $course_oid) : array
    {
        $missing = [];
        $training_course = $this->veda_connector->getTrainingCourseApi()->getCourse($course_oid);

        if (is_null($training_course)) {
            return $missing;
        }

        foreach ($training_course->getAusbildungsgangabschnitte() as $segment) {
            if (!$segment->getAbbildungAufELearningPlattform()) {
                $this->veda_logger->debug('Ignoring segment of type: !AbbildungAufELearningPlattform');
                continue;
            }

            if ($segment->getAusbildungsgangabschnittsart() == self::REMOTE_SESSION_TYPE) {
                $this->veda_logger->debug('Ignoring segment of type: ' . $segment->getAusbildungsgangabschnittsart());
                continue;
            }
            $found_local = false;
            foreach ($exercises as $index => $node) {
                $local_id = $node['vedaid'];
                $remote_id = $segment->getOid();
                if (ilVedaUtils::compareOidsEqual($local_id, $remote_id)) {
                    $found_local = true;
                    break;
                }
            }
            if (!$found_local) {
                $missing[$segment->getOid()] = $segment->getBezeichnung();
            }
        }

        return $missing;
    }

    public function testConnection() : bool
    {
        if (!is_null($this->veda_connector->getElearningPlattformApi()->requestParticipants())) {
            $id = $this->md_db_manager->findTrainingCourseId(70);
            $this->veda_logger->notice($id . ' is the training course id');
            return true;
        }
        return false;
    }
}
