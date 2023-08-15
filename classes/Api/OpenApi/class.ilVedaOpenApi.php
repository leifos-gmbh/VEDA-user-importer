<?php

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
    protected ilVedaConnectorSettings $veda_settings;
    protected ilVedaUserRepositoryInterface $user_repo;
    protected ilVedaCourseRepositoryInterface $crs_repo;
    protected ilVedaMDClaimingPluginDBManagerInterface $md_db_manager;
    protected ilLogger $veda_logger;
    protected ilVedaConnectorPlugin $veda_plugin;
    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;
    protected ilVedaCourseBuilderFactory $crs_builder_factory;

    public function __construct()
    {
        global $DIC;
        $il_db = $DIC->database();
        $object_definition = $DIC['objDefinition'];
        $rbac_admin = $DIC->rbac()->admin();
        $rbac_review = $DIC->rbac()->review();
        $user = $DIC->user();
        $repo_factory = new ilVedaRepositoryFactory();
        $segment_repo = $repo_factory->getSegmentRepository();
        $mail_segment_repo = $repo_factory->getMailRepository();
        $this->mail_segment_builder_factory = new ilVedaMailSegmentBuilderFactory($mail_segment_repo);
        $this->crs_repo = $repo_factory->getCourseRepository();
        $this->md_db_manager = $repo_factory->getMDClaimingPluginRepository();
        $this->veda_plugin = ilVedaConnectorPlugin::getInstance();
        $this->veda_logger = $DIC->logger()->vedaimp();
        $this->crs_builder_factory = new ilVedaCourseBuilderFactory($this->crs_repo, $this->veda_logger);
        $this->user_repo = $repo_factory->getUserRepository();
        $usr_builder_factory = new ilVedaUserBuilderFactory($this->user_repo, $this->veda_logger);
        $this->veda_settings = ilVedaConnectorSettings::getInstance();
        $this->veda_connector = new ilVedaConnector(
            $this->veda_logger,
            $this->veda_settings,
            $this->mail_segment_builder_factory
        );
        $this->sifa_course_import_adapter = new ilVedaCourseImportAdapter(
            $user,
            $object_definition,
            $rbac_admin,
            $rbac_review,
            $this->veda_logger,
            $this->veda_connector,
            $this->crs_builder_factory,
            $segment_repo,
            $this->md_db_manager,
            $this->veda_settings,
            $this->mail_segment_builder_factory
        );
        $this->standard_course_import_adapter = new ilVedaCourseStandardImportAdapter(
            $user,
            $object_definition,
            $this->veda_logger,
            $this->veda_settings,
            $this->crs_builder_factory,
            $this->veda_connector,
            $this->mail_segment_builder_factory
        );
        $this->sifa_member_import_adapter = new ilVedaMemberImportAdapter(
            $this->veda_logger,
            $rbac_admin,
            $rbac_review,
            $this->veda_connector,
            $this->veda_plugin->getUDFClaimingPlugin(),
            $this->md_db_manager,
            $usr_builder_factory,
            $this->crs_builder_factory,
            $this->mail_segment_builder_factory
        );
        $this->standard_member_import_adapter = new ilVedaMemberStandardImportAdapter(
            $this->veda_logger,
            $rbac_admin,
            $this->veda_connector,
            $this->mail_segment_builder_factory
        );
        $this->user_import_adapter = new ilVedaUserImportAdapter(
            $this->veda_logger,
            $this->veda_settings,
            $usr_builder_factory,
            $this->user_repo,
            $this->veda_connector,
            $this->mail_segment_builder_factory
        );
    }

    public function handleParticipantAssignedToCourse(int $obj_id, int $usr_id, int $role_id) : void
    {
        $this->veda_logger->debug('Start handling participant assigned to course');
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
        $this->veda_logger - debug('Send assigned usr:' . $veda_usr->getOid() . 'to crs:' . $veda_crs->getOid());
        $this->veda_connector->sendParticipantAssignedToCourse(
            $veda_crs->getOid(),
            $veda_usr->getOid()
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
            $this->veda_logger->debug('Ignoring every learning progress status except failed and completed.');
            return;
        }
        if (
            !ilObjCourse::_exists($obj_id) ||
            !ilObjUser::_exists($usr_id) ||
            is_null(($usr_oid = ilObjUser::_lookupImportId($usr_id))) ||
            is_null(($crs_oid = ilObjCourse::_lookupImportId($obj_id)))
        ) {
            $this->veda_logger->debug('Course or user with given ids do not exist or import id is missing.');
            return;
        }

        $veda_crs = $this->crs_builder_factory->buildCourse()
            ->withOID($crs_oid)
            ->get();

        if (!$veda_crs->getDocumentSuccess()) {
            $this->veda_logger->debug('Document success is not enabled for course with oid:' . $crs_oid);
            return;
        }

        if ($status === ilLPStatus::LP_STATUS_FAILED_NUM) {
            $this->veda_logger->debug('Sending course failed via api.');
            $this->veda_connector->sendCourseFailed($crs_oid, $usr_oid);
        }

        if ($status === ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            $this->veda_logger->debug('Sending course passed via api.');
            $this->veda_connector->sendCoursePassed($crs_oid, $usr_oid);
        }
    }

    public function handleTrackingEvent(int $obj_id, int $usr_id, int $status) : void
    {
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
        $this->veda_connector->handlePasswordChange($usr_id);
    }

    public function deleteDeprecatedILIASUsers() : void
    {
        $user_db_manager = (new ilVedaRepositoryFactory())->getUserRepository();
        foreach ($this->user_repo->lookupAllUsers() as $user) {
            $found_remote = false;
            foreach ($this->veda_connector->getParticipants() as $participant) {
                if (ilVedaUtils::compareOidsEqual($user->getOid(), $participant->getTeilnehmer()->getOid())) {
                    $found_remote = true;
                }
            }
            if (!$found_remote) {
                $user_db_manager->deleteUserByOID($user->getOid());
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
            try {
                if ($fail->getType() == ilVedaCourseType::SIFA) {
                    $message = 'SIFA course cloning failed, course oid: ' . $fail->getOid();
                    $this->veda_connector->sendCourseCreationFailed($oid);
                } elseif ($fail->getType() == ilVedaCourseType::STANDARD) {
                    $message = 'Standard course cloning failed, course oid: ' . $fail->getOid();
                    $this->veda_connector->sendStandardCourseCreationFailed(
                        $oid,
                        $this->veda_connector::COURSE_CREATION_FAILED_ELARNING
                    );
                } else {
                    $message = 'Unknown course cloning failed, course oid: ' . $fail->getOid();
                    $this->veda_logger->error('Unknown type given for oid ' . $fail->getOid());
                }
                $this->crs_builder_factory->buildCourse()
                    ->withOID($fail->getOid())
                    ->withModified(time())
                    ->withStatusCreated(ilVedaCourseStatus::FAILED)
                    ->store();
            } catch (Exception $e) {
                $this->veda_logger->error($e->getMessage());
            }
            $this->mail_segment_builder_factory->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
        }
    }

    public function importILIASUsers() : void
    {
        $participants = $this->veda_connector->getParticipants();
        $this->veda_logger->dump($participants, \ilLogLevel::DEBUG);
        $this->user_import_adapter->import($participants);
    }

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
        try {
            $training_course = $this->veda_connector->getTrainingCourse($course_oid);
            $this->veda_logger->dump($training_course);
        } catch (ilVedaConnectionException $e) {
            return false;
        }
        return true;
    }

    public function validateLocalSessions(array $sessions, string $course_oid) : array
    {
        $missing = [];
        $training_course = $this->veda_connector->getTrainingCourse($course_oid);
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
        $training_course = $this->veda_connector->getTrainingCourse($course_oid);
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
        $training_course = $this->veda_connector->getTrainingCourse($course_oid);
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
        $training_course = $this->veda_connector->getTrainingCourse($course_oid);
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
        try {
            $response = $this->veda_connector->getParticipants();
            $id = $this->md_db_manager->findTrainingCourseId(70);
            $this->veda_logger->notice($id . ' is the training course id');
        } catch (\Exception $e) {
            $this->veda_logger->warning('Connection test failed with message: ' . $e->getMessage());
            return false;
        }
        return true;
    }
}
