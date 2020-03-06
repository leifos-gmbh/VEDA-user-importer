<?php

use Swagger\Client\Model\Ausbildungszug;

/**
 * Course import adapater
 */
class ilVedaCourseImportAdapter
{
	protected const CP_INFO_AUSBILDUNGSGANG = 1;
	protected const CP_INFO_AUSBILDUNGSZUG = 2;
	protected const CP_INFO_NAME = 3;

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
	 * ilVedaCourseImportAdapter constructor.
	 */
	public function __construct()
	{

		$this->plugin = \ilVedaConnectorPlugin::getInstance();
		$this->logger = $this->plugin->getLogger();
		$this->settings = \ilVedaConnectorSettings::getInstance();
		$this->mdhelper = \ilVedaMDHelper::getInstance();

	}

	/**
	 * Import "trains"
	 * @throws \ilVedaConnectionException
	 */
	public function import()
	{
		foreach($this->mdhelper->findTrainingCourseTemplates() as $ref_id) {
			$this->importTrainingCourse($ref_id);
		}
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
			' of type: ' . \ilObject::_lookupType($source_id, true)
		);

		$options = \ilCopyWizardOptions::_getInstance($copy_id);
		$tc = $options->getTrainingCourseInfo();

		if(!is_array($tc) || !count($tc)) {
			$this->logger->debug('Ignoring non training course copy');
			return;
		}

		$train = $this->readTrainingCourseTrainFromCopyInfo($tc);
		if(!$train instanceof Ausbildungszug) {
			$this->logger->notice('Reading remote info failed.');
			$this->logger->dump($train, \ilLogLevel::NOTICE);
			return;
		}

		$source = \ilObjectFactory::getInstanceByRefId($source_id, false);
		if($source instanceof \ilObjCourse) {

			$target = \ilObjectFactory::getInstanceByRefId($target_id, false);
			if($target instanceof \ilObjCourse) {
				$this->updateCourseCreatedStatus($train->getOid());
				$this->updateCourseAdministrators($source,$target);
			}
			else {
				$this->logger->notice('Target should be course type: ' . $target_id);
			}
		}
		else {
			$this->logger->debug('Nothing todo for non-course copy.');
		}
	}

	/**
	 * Copy admins from source to target
	 *
	 * @param \ilObjCourse $source
	 * @param \ilObjCourse $target
	 */
	protected function updateCourseAdministrators(\ilObjCourse $source, \ilObjCourse $target)
	{
		$source_part = \ilParticipants::getInstance($source->getRefId());
		$target_part = \ilParticipants::getInstance($target->getRefId());

		if(
			(!$target_part instanceof \ilCourseParticipants) ||
			(!$source_part instanceof \ilCourseParticipants)
		) {
			$this->logger->warning('cannot instantiate participants for course: ' . $source->getRefId() . ' ' . $target->getRefId());
			return false;
		}

		foreach($source_part->getAdmins() as $admin_id) {
			$target_part->add($admin_id, ilCourseConstants::CRS_ADMIN);
		}
	}

	/**
	 * @param string $oid
	 */
	protected function updateCourseCreatedStatus(string $oid)
	{
		$connector = \ilVedaConnector::getInstance();
		try {
			$connector->sendTrainingCourseTrainCreated($oid);

			$course_status = new \ilVedaCourseStatus($oid);
			$course_status->setCreationStatus(\ilVedaCourseStatus::STATUS_SYNCHRONIZED);
			$course_status->save();
		}
		catch(\ilVedaConnectionException $e) {
			$this->logger->error('Cannot send course creation status');
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
			' of type: ' . \ilObject::_lookupType($a_source_id, true)
		);

		$options = \ilCopyWizardOptions::_getInstance($a_copy_id);
		$tc = $options->getTrainingCourseInfo();


		if(!is_array($tc) || !count($tc)) {
			$this->logger->debug('Ignoring non training course copy');
			return;
		}

		$this->logger->dump($tc);

		$train = $this->readTrainingCourseTrainFromCopyInfo($tc);
		if(!$train instanceof Ausbildungszug) {
			return;
		}

		$source = \ilObjectFactory::getInstanceByRefId($a_source_id, false);
		if($source instanceof \ilObjCourse) {
			// update md id
			$this->mdhelper->migrateTrainingCoursetoTrain($a_source_id, $a_target_id, $train);

			$target = \ilObjectFactory::getInstanceByRefId($a_target_id, false);
			if($target instanceof \ilObjCourse) {

				$course_status = new \ilVedaCourseStatus($train->getOid());
				$course_status->setObjId($target->getId());
				$course_status->setCreationStatus(\ilVedaCourseStatus::STATUS_PENDING);
				$course_status->save();

				$this->logger->debug('Update title');
				$target->setTitle($tc[self::CP_INFO_NAME]);
				$target->setOfflineStatus(true);
				$target->update();
				$this->createDefaultCourseRole($target, $this->settings->getPermanentSwitchRole(),$train);
				$this->createDefaultCourseRole($target, $this->settings->getTemporarySwitchRole(),$train);

				// delete connection user from administrator role
				$this->deleteAdministratorAssignments($target);
			}
		}
		if($source instanceof \ilObjGroup) {
			$target = \ilObjectFactory::getInstanceByRefId($a_target_id, false);
			if($target instanceof \ilObjGroup) {
				// delete connection user from administrator role
				$this->deleteAdministratorAssignments($target);
			}
		}
		if($source instanceof \ilObject) {
			$this->mdhelper->migrateTrainingCourseSegmentToTrain($a_source_id, $a_target_id, $train, $tc[self::CP_INFO_AUSBILDUNGSGANG]);
		}
		if($source instanceof \ilObjSession) {
			$this->migrateSessionAppointments($a_target_id, $train);
		}
		if($source instanceof \ilObjExercise) {
			$this->migrateExerciseAppointments($a_target_id, $train);
		}

	}

	/**
	 * @param \ilObject $target
	 */
	protected function deleteAdministratorAssignments(\ilObject $target)
	{
		$participants = \ilParticipants::getInstance($target->getRefId());
		foreach($participants->getAdmins() as $admin_id) {
			$participants->delete($admin_id);
		}
	}

	/**
	 * @param \ilObjCourse $course
	 * @param int $rolt_id
	 * @param \Swagger\Client\Model\Ausbildungszug $train
	 * @return \ilObjRole
	 */
	protected function createDefaultCourseRole(\ilObjCourse $course, int $rolt_id, Ausbildungszug $train)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();
		$review = $DIC->rbac()->review();

		$role = new \ilObjRole();
		$role->setTitle(\ilObject::_lookupTitle($rolt_id));
		$role->create();

		$this->logger->debug('Created new local role');

		$admin->assignRoleToFolder($role->getId(),$course->getRefId(),'y');
		$admin->copyRoleTemplatePermissions(
			$rolt_id,
			ROLE_FOLDER_ID,
			$course->getRefId(),
			$role->getId()
		);

		$ops = $review->getOperationsOfRole(
			$role->getId(),
			ilObject::_lookupType($course->getRefId(), true),
			$course->getRefId()
		);
		$admin->grantPermission(
			$role->getId(),
			$ops,
			$course->getRefId()
		);

		switch($rolt_id) {
			case $this->settings->getTemporarySwitchRole():
				$course_status = new ilVedaCourseStatus($train->getOid());
				$course_status->setTemporarySwitchRole($role->getId());
				$course_status->setCreationStatus(\ilVedaCourseStatus::STATUS_PENDING);
				$course_status->save();
				break;
			case $this->settings->getPermanentSwitchRole():
				$course_status = new ilVedaCourseStatus($train->getOid());
				$course_status->setPermanentSwitchRole($role->getId());
				$course_status->setCreationStatus(\ilVedaCourseStatus::STATUS_PENDING);
				$course_status->save();
				break;

			default:
				$this->logger->error('Invalid role id given: ' . $rolt_id);
		}
		return $role;
	}

	/**
	 * @param int $target_id
	 * @param \Swagger\Client\Model\Ausbildungszug $train
	 */
	protected function migrateSessionAppointments(int $target_id, Ausbildungszug $train)
	{
		$session = \ilObjectFactory::getInstanceByRefId($target_id, false);
		if(!$session instanceof \ilObjSession) {
			$this->logger->error('Cannot initiate session with id: ' . $target_id);
			return;
		}
		$app = $session->getFirstAppointment();

		$segment_id = $this->mdhelper->findTrainSegmentId($session->getRefId());

		if(!$segment_id)
		{
			$this->logger->debug('No md mapping found for target_id: ' . $target_id);
			return;
		}

		foreach($train->getAusbildungszugabschnitte() as $train_segment) {

			$segment_begin  = null;
			$segment_end = null;
			if($train_segment->getOid() == $segment_id) {

				$segment_begin = $train_segment->getBeginn();
				$segment_end = $train_segment->getEnde();
			}
			if($segment_begin instanceof DateTime) {
				$this->logger->debug('Update starting time of session');
				$app->setStart(new ilDateTime($segment_begin->getTimestamp(), IL_CAL_UNIX));
			}
			if($segment_end instanceof DateTime) {
				$this->logger->debug('Update ending time of session');
				$app->setEnd(new ilDateTime($segment_end->getTimestamp(),IL_CAL_UNIX));
			}
			$app->update();
		}
	}

	/**
	 * @param int $target_id
	 * @param \Swagger\Client\Model\Ausbildungszug $train
	 */
	protected function migrateExerciseAppointments(int $target_id, Ausbildungszug $train)
	{
		$exercise = \ilObjectFactory::getInstanceByRefId($target_id, false);
		if(!$exercise instanceof \ilObjExercise) {
			$this->logger->error('Cannot initiate exercise with id: ' . $target_id);
			return;
		}

		$segment_id = $this->mdhelper->findTrainSegmentId($exercise->getRefId());

		if(!$segment_id)
		{
			$this->logger->debug('No md mapping found for target_id: ' . $target_id);
			return;
		}


		$segment_start = $segment_end = null;
		foreach($train->getAusbildungszugabschnitte() as $train_segment) {

			$segment_start = $segment_end = null;
			if($train_segment->getOid() == $segment_id) {

				$segment_start = $train_segment->getBeginn();
				$segment_end = $train_segment->getBearbeitungsende();
				if(!$segment_end instanceof DateTime) {
					$segment_end = $train_segment->getEnde();
				}
			}
			if($segment_start instanceof DateTime) {
				$this->logger->debug('Update starting time of exercise');
				foreach(\ilExAssignment::getInstancesByExercise($exercise->getId()) as $assignment) {
					//$assignment->setStartTime($segment_start->getTimestamp());
					//$assignment->update();
				}

			}
			if($segment_end instanceof DateTime) {
				$this->logger->debug('Update deadline  of exercise');
				foreach(\ilExAssignment::getInstancesByExercise($exercise->getId()) as $assignment) {

				    if ($assignment->getDeadlineMode() == \ilExAssignment::DEADLINE_RELATIVE) {
				        $assignment->setRelDeadlineLastSubmission($segment_end->getTimestamp());
				        $assignment->update();
                    }
				    else {
                        $assignment->setDeadline($segment_end->getTimestamp());
                        $assignment->update();
                    }
				}
			}
		}
	}


	/**
	 * @param array $info
	 * @return Ausbildungszug
	 */
	protected function readTrainingCourseTrainFromCopyInfo(array $info) : ?Ausbildungszug
	{
		$connector = \ilVedaConnector::getInstance();
		try {
			$trains = $connector->getTrainingCourseTrains($info[self::CP_INFO_AUSBILDUNGSGANG]);
			foreach($trains as $train) {
				if($train->getOid() == $info[self::CP_INFO_AUSBILDUNGSZUG]) {
					return $train;
				}
			}
			$this->logger->warning('Cannot read training course train for training course id: ' . $info[self::CP_INFO_AUSBILDUNGSZUG]);
			return null;

		}
		catch (ilVedaConnectionException $e) {
			$this->logger->error('Cannot read training course train for training course id: ' . $info[self::CP_INFO_AUSBILDUNGSGANG]);
		}
		return null;
	}


	/**
	 * @param int $ref_id
	 * @throws \ilVedaConnectionException
	 */
	protected function importTrainingCourse(int $ref_id)
	{
		$training_course_id = $this->mdhelper->findTrainingCourseId($ref_id);

		$this->logger->debug('Importing ref_id: ' . $ref_id . ' with training course id: ' . $training_course_id);

		try {
			$connector = \ilVedaConnector::getInstance();
			$trains = $connector->getTrainingCourseTrains($training_course_id);

			foreach($trains as $train) {
				$this->handleTrainingCourseTrainUpdate($ref_id, $train);
			}
		}
		catch(ilVedaConnectionException $e)
		{
			throw $e;
		}
	}

	/**
	 * @param \Swagger\Client\Model\Ausbildungszug $train
	 */
	protected function handleTrainingCourseTrainUpdate(int $source_id, Ausbildungszug $train)
	{
		// check if alread imported
		$train_id = $this->mdhelper->findTrainingCourseTrain($train->getOid());
		if($train_id) {
			$this->logger->info('Ignoring oid: ' . $train->getOid() . ' => "Ausbildungszug" already imported');
			return;
		}

		$this->logger->info('Creating new "Ausbildungszug with oid: ' . $train->getOid());
		$this->copyTrainingCourse($source_id, $train);
	}


	/**
	 * @param $crs_info
	 * @param $parent_id
	 * @return bool|int|mixed
	 * @throws \ilDatabaseException
	 * @throws \ilObjectNotFoundException
	 * @throws \ilSaxParserException
	 */
	protected function copyTrainingCourse(int $ref_id, Ausbildungszug $train)
	{
		global $DIC;

		$user = $DIC->user();

		$parent_id = $this->settings->getImportDirectory();

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
		foreach($GLOBALS['DIC']->repositoryTree()->getSubTree($node_data,false) as $node)
		{
		    $objDefinition = $DIC['objDefinition'];
		    if (!$objDefinition->allowCopy($node['type'])) {
		        $this->logger->notice('Copying is not supported for object type: ' . $node['type']);
		        continue;
            }


			$copy_writer->xmlElement(
				'Option',
				array(
					'id' => $node,
					'action' => 'COPY'
				)
			);
		}

		$copy_writer->xmlEndTag('Settings');

		include_once './webservice/soap/classes/class.ilCopyWizardSettingsXMLParser.php';
		$xml_parser = new \ilCopyWizardSettingsXMLParser($copy_writer->xmlDumpMem(false));
		try {
			$xml_parser->startParsing();
		}
		catch (ilSaxParserException $se)
		{
			$this->logger->error($se->getMessage());
			throw $se;
		}

		$options = $xml_parser->getOptions();

		$source_object = ilObjectFactory::getInstanceByRefId($ref_id);
		if($source_object instanceof \ilObjCourse)
		{
			$session_id = $GLOBALS['DIC']['ilAuthSession']->getId();
			$client_id = CLIENT_ID;


			// Save wizard options
			$copy_id = ilCopyWizardOptions::_allocateCopyId();
			$wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
			$wizard_options->saveOwner($user->getId());
			$wizard_options->saveRoot($ref_id);

			$copy_info = [
				self::CP_INFO_AUSBILDUNGSGANG => $train->getAusbildungsgangId(),
				self::CP_INFO_AUSBILDUNGSZUG => $train->getOid(),
				self::CP_INFO_NAME => $train->getName()
			];

			$wizard_options->saveTrainingCourseInfo($copy_info);

			// add entry for source container
			$wizard_options->initContainer($ref_id, $parent_id);

			foreach($options as $source_id => $option)
			{
				$wizard_options->addEntry($source_id,$option);
			}
			$wizard_options->read();
			$wizard_options->storeTree($ref_id);

			$new_session_id = ilSession::_duplicate($session_id);
			$soap_client = new ilSoapClient();
			$soap_client->setResponseTimeout(600);
			$soap_client->enableWSDL(true);

			if($soap_client->init())
			{
				ilLoggerFactory::getLogger('obj')->info('Calling soap clone method');
				$soap_client->call('ilClone',array($new_session_id.'::'.$client_id, $copy_id));
			}
			else
			{
				$this->logger->error('Copying failed: soap init failed');
			}
		}
		return 0;
	}

}