<?php

/**
 * VEDA user importer plugin cron job class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorCronJob extends ilCronJob
{
        /**
     * @var ilVedaConnectorSettings|null
     */
    private $settings = null; // [ilCronHookPlugin]
    /**
     * @var ilLogger|null
     */
    private $logger = null;
protected $plugin;

    /**
     * ilVedaConnectorCronJob constructor.
     */
    public function __construct()
    {
        $this->settings = \ilVedaConnectorSettings::getInstance();
        $this->logger   = \ilVedaConnectorPlugin::getInstance()->getLogger();
    }

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
    public function getTitle() : string
    {
        return \ilVedaConnectorPlugin::PNAME;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return \ilVedaConnectorPlugin::getInstance()->txt('cron_job_info');
    }

    /**
     * @return int
     */
    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_HOURS;
    }

    /**
     * @return int
     */
    public function getDefaultScheduleValue() : int
    {
        return $this->settings->getCronInterval();
    }

    /**
     * @return bool
     */
    public function hasAutoActivation() : bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function hasCustomSettings() : bool
    {
        return false;
    }

    /**
     * @return \ilCronJobResult
     */
    public function run() : ilCronJobResult
    {
        $result = new ilCronJobResult();

        try {
            $importer = new \ilVedaImporter();
            $importer->setImportMode(true);
            $importer->import();
            $this->settings->updateLastCronExecution();
            $result->setStatus(ilCronJobResult::STATUS_OK);
        } catch (Exception $e) {
            $result->setStatus(ilCronJobResult::STATUS_CRASHED);
            $result->setMessage($e->getMessage());

            $this->logger->warning('Cron update failed with message: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * @return \ilVedaConnectorPlugin
     */
    public function getPlugin() : \ilVedaConnectorPlugin
    {
        return \ilVedaConnectorPlugin::getInstance();
    }

}

?>