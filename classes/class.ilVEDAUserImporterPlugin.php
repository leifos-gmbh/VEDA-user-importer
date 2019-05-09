<?php

/**
 * VEDA user importer plugin base class
 * @author Jesus Lopez <lopez@leifos.de>
 */
class ilVEDAUserImporterPlugin extends ilCronHookPlugin
{
	private static $instance = null;

	const PNAME = 'VEDAUserImporter';
	const SLOT_ID = 'crnhk';
	const CNAME = 'Cron';
	const CTYPE = 'Services';

	function getPluginName(): string
	{
		return self::PNAME;
	}

	public static function getInstance(): ilVEDAUserImporterPlugin
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

	//has to return an array with instances of all cron jobs of the plugin
	function getCronJobInstances(): array
	{
		$job = new ilVEDAUserImporterCronJob();

		ilVEDAUserImporterLogger::getLogger()->write("getinstance new job-> ".$job->getId());
		return array($job);
	}

	//has to return a single instance of the cron job with the given id
	function getCronJobInstance($a_job_id): ilVEDAUserImporterCronJob
	{
		$job = new ilVEDAUserImporterCronJob();

		return $job;
	}

	protected function init()
	{
		$this->initAutoLoad();
	}

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
	}
}
