<?php

use Swagger\Client\Model\Ausbildungszug;

/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 10.10.19
 * Time: 15:30
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
				$target->setTitle($tc[self::CP_INFO_NAME]);
				$target->setOfflineStatus(false);
				$target->update();
			}
		}
		if($source instanceof \ilObject) {
			$this->mdhelper->migrateTrainingCourseSegmentToTrain($a_source_id, $a_target_id, $train);
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