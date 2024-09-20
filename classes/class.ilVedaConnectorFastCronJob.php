<?php

/**
 * @ilCtrl_isCalledBy ilVedaConnectorCronJob: ilObjComponentSettingsGUI
 * VEDA user importer plugin cron job class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorFastCronJob extends ilCronJob
{
    private const CRONJOB_ID_POSTFIX = '_fast';
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
        return ilVedaConnectorPlugin::getInstance()->getId()  . self::CRONJOB_ID_POSTFIX;
    }

    public function getTitle() : string
    {
        return ilVedaConnectorPlugin::PNAME;
    }

    public function getDescription() : string
    {
        return ilVedaConnectorPlugin::getInstance()->txt('cron_job_fast_info');
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_MINUTES;
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
            $importer->import(
                ilVedaImporter::IMPORT_TYPE_UNDEFINED,
                false,
                [
                    ilVedaImporter::IMPORT_USR_INCREMENTAL,
                    ilVedaImporter::IMPORT_MEM
                ]
            );
            $this->settings->updateLastCronExecution();
            $mail_manager = new ilVedaMailManager();
            $mail_manager->sendStatus();
            $result->setStatus(ilCronJobResult::STATUS_OK);
        } catch (ilVedaImporterLockedException $e) {
            // Ignore this lock exception, since the main cron job might be running.
            $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
            $result->setMessage('Cronjob locked');
            return $result;
        } catch (Exception $e) {
            $result->setStatus(ilCronJobResult::STATUS_CRASHED);
            $result->setMessage($e->getMessage());
            $this->logger->warning('Cron update failed with message: ' . $e->getMessage());
        }
        return $result;
    }
}
