<?php

/**
 * VEDA user importer plugin cron job class
 * @author Jesus Lopez <lopez@leifos.de>
 */
class ilVEDAUserImporterCronJob extends ilCronJob
{
	protected $plugin; // [ilCronHookPlugin]

	public function getId()
	{
		return ilVEDAUserImporterPlugin::getInstance()->getId();
	}

	public function getTitle()
	{
		return ilVEDAUserImporterPlugin::PNAME;
	}

	public function getDescription()
	{
		return ilVEDAUserImporterPlugin::getInstance()->txt("cron_job_info");
	}

	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}

	public function getDefaultScheduleValue()
	{
		return ilVEDAUserImporterSettings::getInstance()->getCronInterval();
	}

	public function hasAutoActivation()
	{
		return false;
	}

	public function hasFlexibleSchedule()
	{
		return false;
	}

	public function hasCustomSettings()
	{
		return false;
	}

	public function run()
	{
		$result = new ilCronJobResult();

		try
		{
			//Execute the import
			ilVEDAUserImporterSettings::getInstance()->updateLastCronExecution();
			$result->setStatus(ilCronJobResult::STATUS_OK);
		}
		catch(Exception $e)
		{
			$result->setStatus(ilCronJobResult::STATUS_CRASHED);
			$result->setMessage($e->getMessage());
			//ilVEDAUserImporterLogger::getLogger()->write("Cron update failed with message: " . $e->getMessage());
		}

		return $result;
	}

	public function getPlugin()
	{
		return ilFhoevImportPlugin::getInstance();
	}

}

?>