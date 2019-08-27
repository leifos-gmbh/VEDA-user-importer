<?php

/**
 * VEDA user importer plugin cron job class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorCronJob extends ilCronJob
{
	protected $plugin; // [ilCronHookPlugin]

	/**
	 * @return string
	 */
	public function getId()
	{
		return \ilVedaConnectorPlugin::getInstance()->getId();
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return \ilVedaConnectorPlugin::PNAME;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return \ilVedaConnectorPlugin::getInstance()->txt('cron_job_info');
	}

	/**
	 * @return int
	 */
	public function getDefaultScheduleType(): int
	{
		return self::SCHEDULE_TYPE_IN_HOURS;
	}

	/**
	 * @return int
	 */
	public function getDefaultScheduleValue(): int
	{
		return ilVEDAUserImporterSettings::getInstance()->getCronInterval();
	}

	/**
	 * @return bool
	 */
	public function hasAutoActivation(): bool
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function hasFlexibleSchedule(): bool
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function hasCustomSettings(): bool
	{
		return false;
	}

	/**
	 * @return \ilCronJobResult
	 */
	public function run(): ilCronJobResult
	{
		$result = new ilCronJobResult();

		try
		{
			$importer = new ilVEDAUserImporter();
			$importer->import();

			ilVEDAUserImporterSettings::getInstance()->updateLastCronExecution();
			$result->setStatus(ilCronJobResult::STATUS_OK);
		}
		catch(Exception $e)
		{
			$result->setStatus(ilCronJobResult::STATUS_CRASHED);
			$result->setMessage($e->getMessage());
			ilVEDAUserImporterLogger::getLogger()->write("Cron update failed with message: " . $e->getMessage());
		}

		return $result;
	}

	/**
	 * @return \ilVEDAUserImporterPlugin
	 */
	public function getPlugin(): \ilVedaConnectorPlugin
	{
		return \ilVedaConnectorPlugin::getInstance();
	}

}

?>