<?php

/**
 * VEDA connector plugin base class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorPlugin extends ilCronHookPlugin
{
	/**
	 * @var null | \ilVedaConnectorPlugin
	 */
	private static $instance = null;


	/**
	 * @var null
	 */
	private $logger = null;

	const PNAME = 'VedaConnector';
	const CTYPE = 'Services';
	const CNAME = 'Cron';
	const SLOT_ID = 'crnhk';


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
}
