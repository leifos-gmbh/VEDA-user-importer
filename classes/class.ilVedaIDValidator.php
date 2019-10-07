<?php

use Swagger\Client\Model\TeilnehmerELearningPlattform;
use Swagger\Client\Model\Ausbildungsgang;

/**
 * Class ilVedaIDVAlidator
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaIDValidator
{
	/**
	 *
	 */
	private const REMOTE_SESSION_TYPE = 'Präsenz';

	private const REMOTE_EXERCISE_TYPE = 'Selbstlernen';

	/**
	 * @var null | \ilVedaIDValidator[]
	 */
	private static $instances = null;

	/**
	 * @var null | \ilVedaMDHelper
	 */
	private $mdhelper = null;

	/**
	 * @var null | \ilVedaConnectorPlugin
	 */
	private $plugin = null;

	/**
	 * @var null | \ilLogger
	 */
	private $logger = null;

	/**
	 * @var int
	 */
	private $ref_id = 0;


	/**
	 * @var string
	 */
	private $training_course_id = '';


	/**
	 * @var \ilTemplate|null
	 */
	private $err_template = null;

	/**
	 * @var null | Ausbildungsgang
	 */
	private $training_course = null;


	/**
	 * ilVedaIDValidator constructor.
	 */
	public function __construct(int $ref_id)
	{
		$this->ref_id = $ref_id;

		$this->plugin = \ilVedaConnectorPlugin::getInstance();
		$this->mdhelper = \ilVedaMDHelper::getInstance();

		$this->logger = $this->plugin->getLogger();


		$this->err_template = $this->plugin->getTemplate('tpl.validation_error.html');
	}

	/**
	 * @param int $ref_id
	 * @return \ilVedaIDValidator
	 */
	public static function getInstanceByRefId(int $ref_id) : \ilVedaIDValidator
	{
		if(!isset(self::$instances[$ref_id]) || (self::$instances[$ref_id] instanceof \ilVedaIDValidator)) {
			self::$instances[$ref_id] = new self($ref_id);
		}
		return self::$instances[$ref_id];
	}

	/**
	 * Validate id settings for subtree
	 */
	public function validate()
	{
		if(!$this->validateTrainingCourseId()) {
			return false;
		}

		$ok = true;

		$sessions = $this->readLocalSessions();

		if(!$this->validateLocalSessions($sessions)) {
			$ok  = false;
		}
		if(!$this->validateRemoteSessions($sessions)) {
			$ok = false;
		}

		$exercises = $this->readLocalExercises();

		if(!$this->validateLocalExercises($exercises)) {
			$ok = false;
		}

		if(!$this->validateRemoteExercises($exercises)) {
			$ok = false;
		}
		return $ok;
	}


	/**
	 * Read local sessions
	 */
	protected function readLocalSessions()
	{
		global $DIC;

		$tree = $DIC->repositoryTree();

		$subtree = $tree->getSubTree(
			$tree->getNodeData($this->ref_id),
			true,
			['sess']
		);

		$sessions = [];
		foreach($subtree as $index => $node) {

			$sessions[$index] = $node;
			$sessions[$index]['vedaid'] = $this->mdhelper->findSegmentId($node['ref_id']);
		}
		return $sessions;
	}

	/**
	 * Read local sessions
	 */
	protected function readLocalExercises()
	{
		global $DIC;

		$tree = $DIC->repositoryTree();

		$subtree = $tree->getSubTree(
			$tree->getNodeData($this->ref_id),
			true,
			['exc']
		);

		$exercises = [];
		foreach($subtree as $index => $node) {

			$exercises[$index] = $node;
			$exercises[$index]['vedaid'] = $this->mdhelper->findSegmentId($node['ref_id']);
		}
		return $exercises;
	}


	/**
	 * @param array $sessions
	 * @return bool
	 */
	protected function validateLocalSessions(array $sessions)
	{
		$missing = [];
		foreach($sessions as $index => $node)
		{
			if(!$node['vedaid']) {
				continue;
			}
			$local_id = $node['vedaid'];
			$found_remote = false;
			foreach($this->training_course->getAusbildungsgangabschnitte() as $segment) {

				if($segment->getAusbildungsgangabschnittsart() != self::REMOTE_SESSION_TYPE) {
					$this->logger->debug('Ignoring type: ' . $segment->getAusbildungsgangabschnittsart());
				}


				$remote_id = $segment->getOid();
				if(strcmp($local_id, $remote_id) === 0) {
					$found_remote = true;
					break;
				}
			}
			if(!$found_remote) {
				$missing[] = $node;
			}
		}

		$this->logger->dump($missing);

		if(count($missing)) {

			foreach($missing as $index => $node) {
				$this->err_template->setCurrentBlock('sess_remote_item');
				$this->err_template->setVariable('SESS_REMOTE_ITEM_TITLE', $node['title']);
				$this->err_template->setVariable('SESS_REMOTE_ITEM_OID', $node['vedaid']);
				$this->err_template->parseCurrentBlock();
			}

			$this->err_template->setCurrentBlock('sess_remote');
			$this->err_template->setVariable('SESS_INFO_REMOTE', $this->plugin->txt('err_val_sess_remote_info'));
			$this->err_template->parseCurrentBlock();
			$this->logger->dump($missing);
			return false;
		}
		return true;
	}

	/**
	 * @param array $sessions
	 * @return bool
	 */
	protected function validateLocalExercises(array $exercises)
	{
		$missing = [];
		foreach($exercises as $index => $node)
		{
			if(!$node['vedaid']) {
				continue;
			}
			$local_id = $node['vedaid'];
			$found_remote = false;
			foreach($this->training_course->getAusbildungsgangabschnitte() as $segment) {

				if($segment->getAusbildungsgangabschnittsart() != self::REMOTE_EXERCISE_TYPE) {
					$this->logger->debug('Ignoring type: ' . $segment->getAusbildungsgangabschnittsart());
				}


				$remote_id = $segment->getOid();
				if(strcmp($local_id, $remote_id) === 0) {
					$found_remote = true;
					break;
				}
			}
			if(!$found_remote) {
				$missing[] = $node;
			}
		}

		if(count($missing)) {

			foreach($missing as $index => $node) {
				$this->err_template->setCurrentBlock('exc_remote_item');
				$this->err_template->setVariable('EXC_REMOTE_ITEM_TITLE', $node['title']);
				$this->err_template->setVariable('EXC_REMOTE_ITEM_OID', $node['vedaid']);
				$this->err_template->parseCurrentBlock();
			}

			$this->err_template->setCurrentBlock('exc_remote');
			$this->err_template->setVariable('EXC_INFO_REMOTE', $this->plugin->txt('err_val_exc_remote_info'));
			$this->err_template->parseCurrentBlock();
			$this->logger->dump($missing);
			return false;
		}
		return true;
	}

	/**
	 * @param array $sessions
	 */
	protected function validateRemoteSessions(array $sessions)
	{
		$missing = [];
		foreach($this->training_course->getAusbildungsgangabschnitte() as $segment) {

			if($segment->getAusbildungsgangabschnittsart() != self::REMOTE_SESSION_TYPE) {
				$this->logger->debug('Ignoring segment of type: ' . $segment->getAusbildungsgangabschnittsart());
				continue;
			}
			$found_local = false;
			foreach($sessions as $index => $node) {
				$local_id = $node['vedaid'];
				$remote_id = $segment->getOid();
				if(strcmp($local_id, $remote_id) === 0) {
					$found_local = true;
					break;
				}
			}
			if(!$found_local) {
				$missing[$segment->getOid()] = $segment->getBezeichnung();
			}
		}

		if(count($missing)) {
			foreach($missing as $oid => $title) {
				$this->err_template->setCurrentBlock('sess_local_item');
				$this->err_template->setVariable('SESS_LOCAL_ITEM_TITLE', $title);
				$this->err_template->setVariable('SESS_LOCAL_ITEM_OID', $oid);
				$this->err_template->parseCurrentBlock();
			}

			$this->err_template->setCurrentBlock('sess_local');
			$this->err_template->setVariable('SESS_INFO_LOCAL', $this->plugin->txt('err_val_sess_local_info'));
			$this->err_template->parseCurrentBlock();
			$this->logger->dump($missing);
			return false;
		}
	}

	/**
	 * @param array $exercises
	 */
	protected function validateRemoteExercises(array $exercises)
	{
		$missing = [];
		foreach($this->training_course->getAusbildungsgangabschnitte() as $segment) {

			if($segment->getAusbildungsgangabschnittsart() != self::REMOTE_EXERCISE_TYPE) {
				$this->logger->debug('Ignoring segment of type: ' . $segment->getAusbildungsgangabschnittsart());
				continue;
			}
			$found_local = false;
			foreach($exercises as $index => $node) {
				$local_id = $node['vedaid'];
				$remote_id = $segment->getOid();
				if(strcmp($local_id, $remote_id) === 0) {
					$found_local = true;
					break;
				}
			}
			if(!$found_local) {
				$missing[$segment->getOid()] = $segment->getBezeichnung();
			}
		}

		if(count($missing)) {
			foreach($missing as $oid => $title) {
				$this->err_template->setCurrentBlock('exc_local_item');
				$this->err_template->setVariable('EXC_LOCAL_ITEM_TITLE', $title);
				$this->err_template->setVariable('EXC_LOCAL_ITEM_OID', $oid);
				$this->err_template->parseCurrentBlock();
			}

			$this->err_template->setCurrentBlock('exc_local');
			$this->err_template->setVariable('EXC_INFO_LOCAL', $this->plugin->txt('err_val_exc_local_info'));
			$this->err_template->parseCurrentBlock();
			$this->logger->dump($missing);
			return false;
		}
	}

	/**
	 * @return string
	 */
	public function getErrorMessage()
	{
		return $this->err_template->get();
	}

	/**
	 * @return bool
	 */
	protected function validateTrainingCourseId()
	{
		$this->training_course_id = $this->mdhelper->findTrainingCourseId($this->ref_id);
		if(!$this->training_course_id) {
			$this->err_template->setVariable('SIMPLE_FAILURE', $this->plugin->txt('err_cal_no_tc_id'));
			return false;
		}

		$connector = \ilVedaConnector::getInstance();
		try {
			$this->training_course = $connector->getTrainingCourseSegments($this->training_course_id);
			$this->logger->dump($this->training_course);
		}
		catch(\ilVedaConnectionException $e) {
			$this->err_template->setVariable('SIMPLE_FAILURE', $this->plugin->txt('err_val_wrong_tc'));
			return false;
		}

		return true;
	}
}