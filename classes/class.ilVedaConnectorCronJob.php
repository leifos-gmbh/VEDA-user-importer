<?php

/**
 * @ilCtrl_isCalledBy ilVedaConnectorCronJob: ilObjComponentSettingsGUI
 * VEDA user importer plugin cron job class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorCronJob extends ilCronJob
{
    private ?ilVedaConnectorSettings $settings;
    private ?ilLogger $logger;

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
            // for 15 minutes try to import until no LockException is thrown
            $utime = time();
            while (($utime + (60 * 30)) > time()) {
                try {
                    $importer = new ilVedaImporter();
                    $importer->import(
                        ilVedaImporter::IMPORT_TYPE_UNDEFINED,
                        false,
                        [
                            ilVedaImporter::IMPORT_USR_ALL,
                            ilVedaImporter::IMPORT_CRS,
                            ilVedaImporter::IMPORT_MEM
                        ]
                    );
                    $this->logger->info("Import performed successfully");
                    break;
                } catch (ilVedaImporterLockedException $e) {
                    $this->logger->info('Import cronjob in execution.');
                    sleep(60);
                    $this->logger->info('Slept 60 seconds. Retrying...');
                }
            }
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
