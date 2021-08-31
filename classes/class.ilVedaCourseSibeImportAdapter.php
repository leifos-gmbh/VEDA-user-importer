<?php

use Swagger\Client\Model\Elearningkurs as ElearningkursAlias;

/**
 * Sibe Course import adapater
 */
class ilVedaCourseSibeImportAdapter
{
    protected const CP_INFO_AUSBILDUNGSGANG = 1;
    protected const CP_INFO_AUSBILDUNGSZUG = 2;
    protected const CP_INFO_NAME = 3;
    protected const CP_INFO_ELEARNING_MASTER_COURSE = 4;
    protected const CP_INFO_ELEARNING_COURSE = 5;

    protected const COPY_ACTION_COPY = 'COPY';
    protected const COPY_ACTION_LINK = 'LINK';

    /**
     * @var null | ilVedaConnectorPlugin
     */
    private $plugin = null;

    /**
     * @var ilLogger|null
     */
    private $logger = null;

    /**
     * @var ilVedaConnectorSettings|null
     */
    private $settings = null;

    /**
     * @var null | ilVedaMDHelper
     */
    private $mdhelper = null;

    /**
     * ilVedaCourseImportAdapter constructor.
     */
    public function __construct()
    {
        $this->plugin   = ilVedaConnectorPlugin::getInstance();
        $this->logger   = $this->plugin->getLogger();
        $this->settings = ilVedaConnectorSettings::getInstance();
        $this->mdhelper = ilVedaMDHelper::getInstance();

    }

    /**
     * Import "trains"
     * @throws \ilVedaConnectionException
     * @throws  \ilVedaCourseImporterException
     */
    public function import()
    {
        $this->importCourses();
    }

    /**
     * @throws \ilVedaConnectionException
     * @throws \ilVedaCourseImporterException
     */
    protected function importCourses()
    {
        try {
            $this->logger->debug('Trying to import sibe courses...');
            $connector = \ilVedaConnector::getInstance();
            $sibe_courses = $connector->getSibeCourses();
            $this->logger->dump($sibe_courses);
            foreach ($sibe_courses as $course) {
                $this->handleCourseUpdate($course);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param ElearningkursAlias $course
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     * @throws ilVedaCourseImporterException
     */
    protected function handleCourseUpdate(ElearningkursAlias $course) : void
    {
        $ref_id = (int) $course->getMasterkurs();
        try {
            $ilCourse = ilObjectFactory::getInstanceByRefId($ref_id, false);
            if (!$ilCourse instanceof ilObjCourse) {
                throw new \ilVedaCourseImporterException('Invalid master course id given');
            }
        } catch (Exception $e) {
            $connector = ilVedaConnector::getInstance();
            $connector->sendSibeCourseCreationFailed($course->getOid(), ilVedaConnector::COURSE_CREATION_FAILED_MASTER_COURSE_MISSING);
            $status = new \ilVedaCourseStatus($course->getOid());
            $status->setType(\ilVedaCourseStatus::TYPE_SIBE);
            $status->setCreationStatus(ilVedaCourseStatus::STATUS_FAILED);
            $status->setModified(time());
            $status->save();
            throw $e;
        }
        $obj_id = \ilObject::_getIdForImportId($course->getOid());
        if ($obj_id) {
            $this->logger->info('Ignoring oid ' . $course->getOid() . ' => ELearningkurs already imported.');
            return;
        }
        $this->logger->info('Creating new "ELearningkurs" with oid: ' . $course->getOid());
        $this->copyTrainingCourse($course);
    }


    /**
     * @param ElearningkursAlias
     * @return bool|int|mixed
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     * @throws ilSaxParserException
     */
    protected function copyTrainingCourse(ElearningkursAlias $course)
    {
        global $DIC;

        $ref_id = (int) $course->getMasterkurs();
        $user = $DIC->user();
        $parent_id = $this->settings->getSibeImportDirectory();

        $copy_writer = new ilXmlWriter();
        $copy_writer->xmlStartTag(
            'Settings',
            array(
                'source_id'      => $ref_id,
                'target_id'      => $parent_id,
                'default_action' => 'COPY'
            )
        );

        $node_data = $GLOBALS['DIC']->repositoryTree()->getNodeData($ref_id);
        foreach ($GLOBALS['DIC']->repositoryTree()->getSubTree($node_data, true) as $node_info) {
            $default_action = self::COPY_ACTION_COPY;

            $objDefinition = $DIC['objDefinition'];
            if (!$objDefinition->allowCopy($node_info['type'])) {
                $this->logger->notice('Copying is not supported for object type: ' . $node_info['type']);
                $this->logger->notice('Changing action to "LINK"');
                $default_action = self::COPY_ACTION_LINK;
            }

            switch ($node_info['type']) {
                case 'lm':
                    $this->logger->info('Copy action for lms changed to LINK');
                    $default_action = self::COPY_ACTION_LINK;
                    break;
            }

            $copy_writer->xmlElement(
                'Option',
                array(
                    'id'     => $node_info['ref_id'],
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
            $session_id = $GLOBALS['DIC']['ilAuthSession']->getId();
            $client_id  = CLIENT_ID;

            // Save wizard options
            $copy_id        = ilCopyWizardOptions::_allocateCopyId();
            $wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
            $wizard_options->saveOwner($user->getId());
            $wizard_options->saveRoot($ref_id);

            $copy_info = [
                self::CP_INFO_ELEARNING_MASTER_COURSE => $course->getMasterkurs(),
                self::CP_INFO_ELEARNING_COURSE => $course->getOid(),
                self::CP_INFO_NAME            => $course->getBezeichnung()
            ];

            $wizard_options->saveTrainingCourseInfo($copy_info);

            // add entry for source container
            $wizard_options->initContainer($ref_id, $parent_id);

            foreach ($options as $source_id => $option) {
                $wizard_options->addEntry($source_id, $option);
            }
            $wizard_options->read();
            $wizard_options->storeTree($ref_id);

            $new_session_id = ilSession::_duplicate($session_id);
            $soap_client    = new ilSoapClient();
            $soap_client->setResponseTimeout(600);
            $soap_client->enableWSDL(true);

            // Add new entry for oid
            $status = new \ilVedaCourseStatus($course->getOid());
            $status->setType(\ilVedaCourseStatus::TYPE_SIBE);
            $status->setModified(time());
            $status->setCreationStatus(\ilVedaCourseStatus::STATUS_PENDING);
            $status->save();

            // send copy start
            try {
                $connector = \ilVedaConnector::getInstance();
                $connector->sendSibeCourseCopyStarted($course->getOid());
            } catch (Exception $e) {
                $this->logger->error('Sending course copy start message failed with message: ' . $e->getMessage());
            }
            if ($soap_client->init()) {
                ilLoggerFactory::getLogger('obj')->info('Calling soap clone method');
                $soap_client->call('ilClone', array($new_session_id . '::' . $client_id, $copy_id));
            } else {
                $this->logger->error('Copying failed: soap init failed');
            }
        }
        return 0;
    }

    /**
     * @param int $source_id
     * @param int $target_id
     * @param int $copy_id
     */
    public function handleAfterCloningDependenciesEvent(int $source_id, int $target_id, int $copy_id)
    {
        $this->logger->debug(
            'Handling afterCloning event for for source_id: ' . $source_id .
            ' of type: ' . ilObject::_lookupType($source_id, true)
        );

        $options = ilCopyWizardOptions::_getInstance($copy_id);
        $tc      = $options->getTrainingCourseInfo();

        if (!is_array($tc) || !count($tc) || !isset($tc[self::CP_INFO_ELEARNING_MASTER_COURSE])) {
            $this->logger->debug('Ignoring non training course copy');
            return;
        }

        $source = ilObjectFactory::getInstanceByRefId($source_id, false);
        if ($source instanceof ilObjCourse) {
            $target = ilObjectFactory::getInstanceByRefId($target_id, false);
            if ($target instanceof ilObjCourse) {
                $this->updateCourseCreatedStatus($tc[self::CP_INFO_ELEARNING_COURSE]);
                $this->updateCourseAdministrators($source, $target);
            } else {
                $this->logger->notice('Target should be course type: ' . $target_id);
            }
        } else {
            $this->logger->debug('Nothing todo for non-course copy.');
        }
    }

    /**
     * @param array $info
     * @return Ausbildungszug
     */
    protected function readTrainingCourseTrainFromCopyInfo(array $info) : ?Ausbildungszug
    {
        $connector = ilVedaConnector::getInstance();
        try {
            $trains = $connector->getTrainingCourseTrains($info[self::CP_INFO_AUSBILDUNGSGANG]);
            foreach ($trains as $train) {
                if (\ilVedaUtils::compareOidsEqual($train->getOid(), $info[self::CP_INFO_AUSBILDUNGSZUG])) {
                    return $train;
                }
            }
            $this->logger->warning('Cannot read training course train for training course id: ' . $info[self::CP_INFO_AUSBILDUNGSZUG]);
            return null;

        } catch (ilVedaConnectionException $e) {
            $this->logger->error('Cannot read training course train for training course id: ' . $info[self::CP_INFO_AUSBILDUNGSGANG]);
        }
        return null;
    }

    /**
     * @param string $oid
     */
    protected function updateCourseCreatedStatus(string $oid)
    {
        $connector = ilVedaConnector::getInstance();
        try {
            $connector->sendSibeCourseCreated($oid);

            $course_status = new ilVedaCourseStatus($oid);
            $course_status->setType(\ilVedaCourseStatus::TYPE_SIBE);
            $course_status->setCreationStatus(ilVedaCourseStatus::STATUS_SYNCHRONIZED);
            $course_status->setModified(time());
            $course_status->save();
        } catch (ilVedaConnectionException $e) {
            $this->logger->error('Cannot send course creation status');
        }
    }

    /**
     * Copy admins from source to target
     * @param ilObjCourse $source
     * @param ilObjCourse $target
     */
    protected function updateCourseAdministrators(ilObjCourse $source, ilObjCourse $target)
    {
        $source_part = ilParticipants::getInstance($source->getRefId());
        $target_part = ilParticipants::getInstance($target->getRefId());

        if (
            (!$target_part instanceof ilCourseParticipants) ||
            (!$source_part instanceof ilCourseParticipants)
        ) {
            $this->logger->warning('cannot instantiate participants for course: ' . $source->getRefId() . ' ' . $target->getRefId());
            return false;
        }

        foreach ($source_part->getAdmins() as $admin_id) {
            $target_part->add($admin_id, ilCourseConstants::CRS_ADMIN);
        }
    }

    /**
     * @param int $a_source_id
     * @param int $a_target_id
     * @param int $a_copy_id
     */
    public function handleAfterCloningEvent(int $a_source_id, int $a_target_id, int $a_copy_id)
    {
        $this->logger->debug(
            'Handling afterCloning event for for source_id: ' . $a_source_id .
            ' of type: ' . ilObject::_lookupType($a_source_id, true)
        );

        $options = ilCopyWizardOptions::_getInstance($a_copy_id);
        $tc      = $options->getTrainingCourseInfo();

        if (!is_array($tc) || !count($tc) || !isset($tc[self::CP_INFO_ELEARNING_MASTER_COURSE])) {
            $this->logger->debug('Ignoring non "ElearningKurs".');
            return;
        }

        $this->logger->dump($tc);

        $source = ilObjectFactory::getInstanceByRefId($a_source_id, false);
        if ($source instanceof ilObjCourse) {
            $target = ilObjectFactory::getInstanceByRefId($a_target_id, false);
            if ($target instanceof ilObjCourse) {

                $oid = $tc[self::CP_INFO_ELEARNING_COURSE];

                $course_status = new ilVedaCourseStatus($oid);
                $course_status->setType(\ilVedaCourseStatus::TYPE_SIBE);
                $course_status->setModified(time());
                $course_status->setObjId($target->getId());
                $course_status->setCreationStatus(ilVedaCourseStatus::STATUS_PENDING);
                $course_status->save();

                $this->logger->debug('Update title');
                $target->setTitle($tc[self::CP_INFO_NAME]);
                $target->setOfflineStatus(true);
                $target->setImportId($oid);
                $target->update();

                // delete connection user from administrator role
                $this->deleteAdministratorAssignments($target);
            }
        }
        if ($source instanceof ilObjGroup) {
            $target = ilObjectFactory::getInstanceByRefId($a_target_id, false);
            if ($target instanceof ilObjGroup) {
                // delete connection user from administrator role
                $this->deleteAdministratorAssignments($target);
            }
        }
    }


    /**
     * @param ilObject $target
     */
    protected function deleteAdministratorAssignments(ilObject $target)
    {
        $participants = ilParticipants::getInstance($target->getRefId());
        foreach ($participants->getAdmins() as $admin_id) {
            $participants->delete($admin_id);
        }
    }

}