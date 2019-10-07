<?php

use Monolog\Handler\StreamHandler;

/**
 * VEDA connector plugin base class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorPlugin extends ilCronHookPlugin implements \ilAppEventListener
{
	protected const USER_SERVICE = 'Services/User';
	protected const EVENT_UPDATE_PASSWORD = 'passwordChanged';
	protected const EVENT_DELETE_USER = 'deleteUser';


	/**
	 * @var null | \ilAdvancedMDClaimingPlugin
	 */
	private $claiming = null;


	/**
	 * @var null | \ilVedaConnectorPlugin
	 */
	private static $instance = null;


	/**
	 * @var null
	 */
	private $logger = null;

	/**
	 * Veda plugin
	 */
	const PNAME = 'VedaConnector';
	const CTYPE = 'Services';
	const CNAME = 'Cron';
	const SLOT_ID = 'crnhk';

	/**
	 * Claiming plugin
	 */
	const CLAIMING_CTYPE = 'Services';
	const CLAIMING_CNAME = 'AdvancedMetaData';
	const CLAIMING_SLOT_ID = 'amdc';
	const CLAIMING_NAME = 'VedaMDClaiming';


	/**
	 * @return \ilVedaConnectorPlugin
	 */
	public static function getInstance(): ilVedaConnectorPlugin
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = ilPluginAdmin::getPluginObject(
			self::CTYPE,
			self::CNAME,
			self::SLOT_ID,
			self::PNAME
		);
	}

	/**
	 * @return string
	 */
	public function getPluginName(): string
	{
		return self::PNAME;
	}


	/**
	 * @return ilVEDAUserImporterCronJob[]
	 */
	public function getCronJobInstances(): array
	{
		return new \ilVedaConnectorCronJob();
	}

	/**
	 * @param $a_job_id
	 * @return \ilVEDAUserImporterCronJob
	 */
	public function getCronJobInstance($a_job_id): ilVEDAUserImporterCronJob
	{
		return new \ilVedaConnectorCronJob();
	}

	/**
	 * @return \ilLogger
	 */
	public function getLogger() : \ilLogger
	{
		return $this->logger;
	}

	/**
	 * Init plugin
	 */
	protected function init() : void
	{
		global $DIC;

		$this->logger = $DIC->logger()->vedaimp();

		require($this->getDirectory().'/vendor/autoload.php');
		$this->initAutoLoad();

		$settings = \ilVedaConnectorSettings::getInstance();
		$this->logger->debug('Set log level to: ' . $settings->getLogLevel());

		if(
			$settings->getLogLevel() != \ilLogLevel::OFF &&
			$settings->getLogFile() != ''
		)
		{
			$stream_handler = new StreamHandler(
				$settings->getLogFile(),
				$settings->getLogLevel(),
				true
			);
			$line_formatter = new ilLineFormatter(\ilLoggerFactory::DEFAULT_FORMAT, 'Y-m-d H:i:s.u',TRUE,TRUE);
			$stream_handler->setFormatter($line_formatter);
			$this->logger->getLogger()->pushHandler($stream_handler);
		}

		// format lines
		foreach($this->logger->getLogger()->getHandlers() as $handler) {
			$handler->setLevel($settings->getLogLevel());
		}

		// init claiming plugin
		$this->claiming = \ilPluginAdmin::getPluginObject(
			self::CLAIMING_CTYPE,
			self::CLAIMING_CNAME,
			self::CLAIMING_SLOT_ID,
			self::CLAIMING_NAME
		);
	}

	/**
	 * @return \ilAdvancedMDClaimingPlugin|null
	 */
	public function getClaimingPlugin()
	{
		return $this->claiming;
	}

	/**
	 * Check if claiming plugin is available and active
	 */
	public function isClaimingPluginAvailable()
	{
		return $this->claiming instanceof \ilVedaMDClaimingPlugin;
	}

	/**
	 * Add autoloading
	 */
	protected function initAutoLoad(): void
	{
		spl_autoload_register(
			array($this,'autoLoad')
		);
	}

	/**
	 * Auto load implementation
	 *
	 * @param string class name
	 */
	private final function autoLoad($a_classname)
	{
		$class_file = $this->getClassesDirectory().'/class.'.$a_classname.'.php';
		if(@include_once($class_file))
		{
			return;
		}
		$exception_file = $this->getExceptionDirectory().'/class.'.$a_classname.'.php';
		if(@include_once($exception_file))
		{
			return;
		}
	}

	/**
	 * @return string
	 */
	private function getExceptionDirectory() : string
	{
		return $this->getDirectory().'/exceptions';
	}

	/**
	 * Handle an event in a listener.
	 *
	 * @param    string $a_component component, e.g. "Modules/Forum" or "Services/User"
	 * @param    string $a_event event e.g. "createUser", "updateUser", "deleteUser", ...
	 * @param    array $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
	 */
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		if(
			$a_component == self::USER_SERVICE &&
			$a_event == self::EVENT_UPDATE_PASSWORD
		)
		{
			\ilVedaConnector::getInstance()->handlePasswordChange($a_parameter['usr_id']);
		}
		if(
			$a_component == self::USER_SERVICE &&
			$a_event == self::EVENT_DELETE_USER
		)
		{
			\ilVedaUserStatus::handleDeleteAccount($a_parameter['usr_id']);
		}
	}
}
