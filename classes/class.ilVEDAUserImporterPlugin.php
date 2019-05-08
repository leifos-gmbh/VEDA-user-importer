<?php
include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");

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

	function getPluginName()
	{
		return self::PNAME;
	}

	/**
	 * Get singelton instance
	 * @global ilPluginAdmin $ilPluginAdmin
	 * @return ilVEDAUserImporterPlugin
	 */
	public static function getInstance()
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
	function getCronJobInstances()
	{
		$job = new ilVEDAUserImporterCronJob();

		ilVEDAUserImporterLogger::getLogger()->write("getinstance new job-> ".$job->getId());
		return array($job);
	}

	//has to return a single instance of the cron job with the given id
	function getCronJobInstance($a_job_id)
	{
		$job = new ilVEDAUserImporterCronJob();

		return $job;
	}

	/**
	 * Init auto load
	 */
	protected function init()
	{
		$this->initAutoLoad();
	}

	/**
	 * Init auto loader
	 * @return void
	 */
	protected function initAutoLoad()
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
