<?php

/**
 * VEDA user importer plugin cron job class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorCronJob extends ilCronJob
{
    private ?ilVedaConnectorSettings $settings = null; // [ilCronHookPlugin]
    private ?ilLogger $logger = null;

    /**
     * ilVedaConnectorCronJob constructor.
     */
    public function __construct()
    {
        $this->settings = ilVedaConnectorSettings::getInstance();
        $this->logger = ilVedaConnectorPlugin::getInstance()->getLogger();
    }

    public function getId() : string
    {
        return ilVedaConnectorPlugin::getInstance()->getId();
    }

    public function getTitle() : string
    {
        return ilVedaConnectorPlugin::PNAME;
    }

    public function getDescription() : string
    {
        return ilVedaConnectorPlugin::getInstance()->txt('cron_job_info');
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_HOURS;
    }

    public function getDefaultScheduleValue() : int
    {
        return $this->settings->getCronInterval();
    }

    public function hasAutoActivation() : bool
    {
        return false;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    public function hasCustomSettings() : bool
    {
        return false;
    }

    public function run() : ilCronJobResult
    {
        $result = new ilCronJobResult();

        try {
            $importer = new ilVedaImporter();
            $importer->setImportMode(true);
            $importer->import();
            $this->settings->updateLastCronExecution();

            $mail_manager = new ilVedaMailManager();
            $mail_manager->sendStatus();

            $result->setStatus(ilCronJobResult::STATUS_OK);
        } catch (Exception $e) {
            $result->setStatus(ilCronJobResult::STATUS_CRASHED);
            $result->setMessage($e->getMessage());
            $this->logger->warning('Cron update failed with message: ' . $e->getMessage());
        }

        return $result;
    }
}
