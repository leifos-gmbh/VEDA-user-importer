<?php

use OpenApi\Client\Model\Ausbildungszug;
use OpenApi\Client\Model\Elearningkurs;

class ilVedaCourseStandardImportAdapter
{
    /**
     * @var int
     */
    protected const CP_INFO_AUSBILDUNGSGANG = 1;
    /**
     * @var int
     */
    protected const CP_INFO_AUSBILDUNGSZUG = 2;
    /**
     * @var int
     */
    protected const CP_INFO_NAME = 3;
    /**
     * @var int
     */
    protected const CP_INFO_ELEARNING_MASTER_COURSE = 4;
    /**
     * @var int
     */
    protected const CP_INFO_ELEARNING_COURSE = 5;
    /**
     * @var string
     */
    protected const COPY_ACTION_COPY = 'COPY';
    /**
     * @var string
     */
    protected const COPY_ACTION_LINK = 'LINK';

    protected ilLogger $logger;
    protected ilVedaConnectorSettings $settings;
    protected ilObjUser $user;
    protected ilObjectDefinition $object_definition;
    protected ilVedaConnector $veda_connector;
    protected ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory;

    public function __construct(
        ilObjUser $user,
        ilObjectDefinition $object_definition,
        ilLogger $veda_logger,
        ilVedaConnectorSettings $veda_settings,
        ilVedaConnector $veda_connector,
        ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory
    ) {
        $this->user = $user;
        $this->object_definition = $object_definition;
        $this->logger = $veda_logger;
        $this->settings = $veda_settings;
        $this->veda_connector = $veda_connector;
        $this->repo_content_builder_factory = $repo_content_builder_factory;
    }

    /**
     * Import "trains"
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     * @throws ilVedaCourseImporterException
     * @throws ilSaxParserException
     */
    public function import() : void
    {
        $this->logger->debug('Trying to import standard courses...');
        $standard_courses = $this->veda_connector->getElearningPlattformApi()->requestCourses();
        if (is_null($standard_courses)) {
            return;
        }
        foreach ($standard_courses as $course) {
            $this->handleCourseUpdate($course);
        }
    }

    /**
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     * @throws ilVedaCourseImporterException
     * @throws ilSaxParserException
     */
    protected function handleCourseUpdate(Elearningkurs $course) : void
    {
        // check if course is created before masterkurs validation, since a valid masterkurs is not required in this case.
        $obj_id = ilObject::_getIdForImportId($course->getOid());
        if ($obj_id) {
            $this->logger->info('Ignoring oid ' . $course->getOid() . ' => ELearningkurs already imported.');
            return;
        }

        $ref_id = (int) $course->getMasterkurs();
        try {
            $ilCourse = ilObjectFactory::getInstanceByRefId($ref_id, false);
            if (!$ilCourse instanceof ilObjCourse) {
                throw new ilVedaCourseImporterException('Invalid master course id given: ' . $ref_id);
            }
        } catch (Exception $e) {
            $this->logger->debug('Exception occurred: ' . $e->getMessage());
            $this->veda_connector->getElearningPlattformApi()->sendCourseCreationFailed(
                $course->getOid(),
                'Masterkurs-Id nicht vorhanden.'
            );
            $this->repo_content_builder_factory->getVedaCourseBuilder()->buildCourse()
                ->withOID($course->getOid())
                ->withType(ilVedaCourseType::STANDARD)
                ->withStatusCreated(ilVedaCourseStatus::FAILED)
                ->withModified(time())
                ->store();
            throw $e;
        }
        $message = 'Creating new "ELearningkurs" with oid: ' . $course->getOid();
        $this->logger->info($message);
        $this->copyTrainingCourse($course);
        $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
            ->withType(ilVedaMailSegmentType::COURSE_UPDATED)
            ->withMessage($message)
            ->store();
    }

    /**
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     * @throws ilSaxParserException
     */
    protected function copyTrainingCourse(Elearningkurs $course) : void
    {
        $ref_id = (int) $course->getMasterkurs();
        $parent_id = $this->settings->getStandardImportDirectory();

        $copy_writer = new ilXmlWriter();
        $copy_writer->xmlStartTag(
            'Settings',
            array(
                'source_id' => $ref_id,
                'target_id' => $parent_id,
                'default_action' => 'COPY'
            )
        );

        $node_data = $GLOBALS['DIC']->repositoryTree()->getNodeData($ref_id);
        foreach ($GLOBALS['DIC']->repositoryTree()->getSubTree($node_data, true) as $node_info) {
            $default_action = self::COPY_ACTION_COPY;

            if (!$this->object_definition->allowCopy($node_info['type'])) {
                $this->logger->notice('Copying is not supported for object type: ' . $node_info['type']);
                $this->logger->notice('Changing action to "LINK"');
                $default_action = self::COPY_ACTION_LINK;
            }

            if ($node_info['type'] === 'lm') {
                $this->logger->info('Copy action for lms changed to LINK');
                $default_action = self::COPY_ACTION_LINK;
            }

            $copy_writer->xmlElement(
                'Option',
                array(
                    'id' => $node_info['ref_id'],
                    'action' => $default_action
                )
            );
        }

        $copy_writer->xmlEndTag('Settings');

        include_once './webservice/soap/classes/class.ilCopyWizardSettingsXMLParser.php';
        $xml_parser = new ilCopyWizardSettingsXMLParser($copy_writer->xmlDumpMem(false));
        try {
            $xml_parser->startParsing();
        } catch (ilSaxParserException $se) {
            $this->logger->error($se->getMessage());
            throw $se;
        }

        $options = $xml_parser->getOptions();

        $source_object = ilObjectFactory::getInstanceByRefId($ref_id);
        if ($source_object instanceof ilObjCourse) {
            $client_id = CLIENT_ID;

            // Save wizard options
            $copy_id = ilCopyWizardOptions::_allocateCopyId();
            $wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
            $settings = ilVedaConnectorSettings::getInstance();
            $soap_user = $settings->getSoapUser();
            $soap_user_id = ilObjUser::_lookupId($soap_user);
            if ($soap_user_id === null) {
                throw new ilVedaConnectionException('soap_connection_failed', ilVedaConnectionException::ERR_SOAP_CONNECTION);
            }
            $wizard_options->saveOwner($soap_user_id);
            $wizard_options->saveRoot($ref_id);

            $copy_info = [
                self::CP_INFO_ELEARNING_MASTER_COURSE => $course->getMasterkurs(),
                self::CP_INFO_ELEARNING_COURSE => $course->getOid(),
                self::CP_INFO_NAME => $course->getBezeichnung()
            ];

            $wizard_options->saveTrainingCourseInfo($copy_info);

            // add entry for source container
            $wizard_options->initContainer($ref_id, $parent_id);

            foreach ($options as $source_id => $option) {
                $wizard_options->addEntry($source_id, $option);
            }
            $wizard_options->read();
            $wizard_options->storeTree($ref_id);

            // init session
            $soap_client = new ilSoapClient();
            $soap_client->setResponseTimeout(600);
            $soap_client->enableWSDL(true);

            // Add new entry for oid
            $this->repo_content_builder_factory->getVedaCourseBuilder()->buildCourse()
                ->withOID($course->getOid(), false)
                ->withType(ilVedaCourseType::STANDARD)
                ->withModified(time())
                ->withStatusCreated(ilVedaCourseStatus::PENDING)
                ->withDocumentSuccess($course->getKursabschlussAlsErfolgDokumentieren())
                ->store();

            // send copy start
            $this->logger->debug('Send copy start');
            if (!$this->veda_connector->getElearningPlattformApi()->sendCourseCopyStarted($course->getOid())) {
                $this->logger->error('Sending course copy start message failed.');
            }
            if ($soap_client->init()) {
                $this->logger->debug('Soap clone method called');
                ilLoggerFactory::getLogger('obj')->info('Calling soap clone method');
                $settings = ilVedaConnectorSettings::getInstance();
                $session_token = $soap_client->call(
                    'login',
                    [
                        CLIENT_ID,
                        $settings->getSoapUser(),
                        $settings->getSoapPassword()
                    ]
                );
                if (stristr($session_token, '::') === false) {
                    throw new ilVedaConnectionException('soap connection failed', ilVedaConnectionException::ERR_SOAP_CONNECTION);
                }
                $soap_client->call('ilClone', array($session_token, $copy_id));
            } else {
                $message = 'Standard course copying failed: soap init failed';
                $this->logger->error($message);
                $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                    ->withMessage($message)
                    ->withType(ilVedaMailSegmentType::ERROR)
                    ->store();
            }
        }
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     */
    public function handleAfterCloningDependenciesEvent(int $source_id, int $target_id, int $copy_id) : void
    {
        $this->logger->debug(
            'Handling afterCloningDependencies event for for source_id: ' . $source_id .
            ' of type: ' . ilObject::_lookupType($source_id, true)
        );

        $options = ilCopyWizardOptions::_getInstance($copy_id);
        $tc = $options->getTrainingCourseInfo();

        if (!is_array($tc) || !count($tc) || !isset($tc[self::CP_INFO_ELEARNING_MASTER_COURSE])) {
            $this->logger->debug('Ignoring non training course copy');
            return;
        }

        $source = ilObjectFactory::getInstanceByRefId($source_id, false);
        $target = ilObjectFactory::getInstanceByRefId($target_id, false);
        if (
            $source instanceof ilObjCourse &&
            $target instanceof ilObjCourse
        ) {
            $this->updateCourseCreatedStatus($tc[self::CP_INFO_ELEARNING_COURSE]);
            $this->copyAdminsFromSourceToTarget($source, $target);
        } elseif (!($target instanceof ilObjCourse)) {
            $this->logger->notice('Target should be course type: ' . $target_id);
        } else {
            $this->logger->debug('Nothing todo for non-course copy.');
        }
    }

    protected function updateCourseCreatedStatus(string $oid) : void
    {
        if ($this->veda_connector->getElearningPlattformApi()->sendCourseCreated($oid)) {
            $this->repo_content_builder_factory->getVedaCourseBuilder()->buildCourse()
                ->withOID($oid)
                ->withType(ilVedaCourseType::STANDARD)
                ->withStatusCreated(ilVedaCourseStatus::SYNCHRONIZED)
                ->withModified(time())
                ->store();
        } else {
            $this->logger->error('Cannot send course creation status');
        }
    }

    protected function copyAdminsFromSourceToTarget(ilObjCourse $source, ilObjCourse $target) : void
    {
        $source_part = ilParticipants::getInstance($source->getRefId());
        $target_part = ilParticipants::getInstance($target->getRefId());

        if (
            (!$target_part instanceof ilCourseParticipants) ||
            (!$source_part instanceof ilCourseParticipants)
        ) {
            $message = 'Cannot instantiate participants for course: ' . $source->getRefId() . ' ' . $target->getRefId();
            $this->logger->warning($message);
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
            return;
        }

        foreach ($source_part->getAdmins() as $admin_id) {
            $target_part->add($admin_id, ilCourseConstants::CRS_ADMIN);
        }
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     */
    public function handleAfterCloningEvent(int $a_source_id, int $a_target_id, int $a_copy_id) : void
    {
        $this->logger->debug(
            'Handling afterCloning event for for source_id: ' . $a_source_id .
            ' of type: ' . ilObject::_lookupType($a_source_id, true)
        );
        $options = ilCopyWizardOptions::_getInstance($a_copy_id);
        $tc = $options->getTrainingCourseInfo();

        if (!is_array($tc) || !count($tc) || !isset($tc[self::CP_INFO_ELEARNING_MASTER_COURSE])) {
            $this->logger->debug('Ignoring non "ElearningKurs".');
            return;
        }
        $oid = $tc[self::CP_INFO_ELEARNING_COURSE];
        $this->logger->dump($tc, ilLogLevel::DEBUG);
        $source = ilObjectFactory::getInstanceByRefId($a_source_id, false);
        $target = ilObjectFactory::getInstanceByRefId($a_target_id, false);
        $standard_courses = $this->veda_connector->getElearningPlattformApi()->requestCourses();
        $elearning_course = $standard_courses->getCourseByOId($oid);
        if (
            $source instanceof ilObjCourse &&
            $target instanceof ilObjCourse
        ) {
            $this->repo_content_builder_factory->getVedaCourseBuilder()->buildCourse()
                ->withOID($oid)
                ->withType(ilVedaCourseType::STANDARD)
                ->withModified(time())
                ->withObjID($target->getId())
                ->withStatusCreated(ilVedaCourseStatus::PENDING)
                ->store();
            $target->setOfflineStatus(true);
            $target->setImportId($oid);
            $target->setTitle($elearning_course->getBezeichnung());
            $target->setDescription($elearning_course->getBeschreibung() ?? '');
            $target = $this->updateCourseAvailability($target, $elearning_course);
            $target = $this->updateCourseEventPeriod($target, $elearning_course);
            $target->update();
            // delete connection user from administrator role
            $this->deleteAdministratorAssignments($target);
        }
        if (
            $source instanceof ilObjGroup &&
            $target instanceof ilObjGroup
        ) {
            // delete connection user from administrator role
            $this->deleteAdministratorAssignments($target);
        }
    }

    protected function updateCourseAvailability(
        ilObjCourse $target,
        Elearningkurs $elearning_course
    ): ilObjCourse {
        $course_start = $elearning_course->getBeginn();
        $course_end = $elearning_course->getEnde();
        if(is_null($course_start) || is_null($course_end)) {
            return $target;
        }
        $target->setActivationStart($course_start->getTimestamp());
        $target->setActivationEnd($course_end->getTimestamp());
        $target->setOfflineStatus(false);
        return $target;
    }

    /**
     * @throws ilDateTimeException
     */
    protected function updateCourseEventPeriod(
        ilObjCourse $target,
        Elearningkurs $elearning_course
    ): ilObjCourse {
        $appointments = $elearning_course->getTermine();
        $appointment_collection = $elearning_course->getTerminreihen();
        $course_period_start = null;
        $course_period_end = null;
        if (is_null($appointments) && is_null($appointment_collection)) {
            return $target;
        }
        if (
            !is_null($appointments) &&
            count($appointments) > 0
        ) {
            $course_period_start = $appointments[0]->getTerminVon();
            $course_period_end = $appointments[0]->getTerminBis();
        }
        if (
            is_null($course_period_start) &&
            is_null($course_period_end) &&
            !is_null($appointment_collection) &&
            count($appointment_collection) > 0
        ) {
            $course_period_start = $appointment_collection[0]->getTerminVon();
            $course_period_end = $appointment_collection[0]->getTerminBis();
        }
        $target->setCoursePeriod(
            new ilDateTime($course_period_start->format(DateTimeInterface::RFC3339), IL_CAL_DATETIME),
            new ilDateTime($course_period_end->format(DateTimeInterface::RFC3339), IL_CAL_DATETIME),
        );
        return $target;
    }

    protected function deleteAdministratorAssignments(ilObject $target) : void
    {
        $participants = ilParticipants::getInstance($target->getRefId());
        foreach ($participants->getAdmins() as $admin_id) {
            $participants->delete($admin_id);
        }
    }
}
