<?php

class ilVedaImporter
{
    public const IMPORT_TYPE_UNDEFINED = 0;
    public const IMPORT_TYPE_SIFA = 1;
    public const IMPORT_TYPE_SIBE = 2;

    public const IMPORT_USR = 'usr';
    public const IMPORT_CRS = 'crs';
    public const IMPORT_MEM = 'mem';

    public const IMPORT_NONE = 0;
    public const IMPORT_ALL = 1;
    public const IMPORT_SELECTED = 2;

    /**
     * @var \ilVedaImporter
     */
    private static $instance = null;

    /**
     * @var \ilLogger|null
     */
    private $logger = null;

    /**
     * @var \ilVedaConnectorSettings|null
     */
    private $settings = null;

    private $plugin = null;

    /**
     * @var string[]
     */
    private $import_modes = [];

    private $import_type = self::IMPORT_TYPE_UNDEFINED;

    /**
     * ilVedaImporter constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->vedaimp();
        $this->settings = \ilVedaConnectorSettings::getInstance();
        $this->plugin = \ilVedaConnectorPlugin::getInstance();
    }

    /**
     * @return \ilVedaImporter
     */
    public static function getInstance() : \ilVedaImporter
    {
        if (!self::$instance instanceof \ilVedaImporter) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setImportType(int $type) : void
    {
        $this->import_type = $type;
    }

    public function getImportType() : int
    {
        return $this->import_type;
    }

    /**
     * @param bool  $all
     * @param array $types
     */
    public function setImportMode(bool $all, array $types = null)
    {
        if ($all) {
            $this->import_modes = [
                self::IMPORT_USR,
                self::IMPORT_CRS,
                self::IMPORT_MEM,
            ];
        } elseif (is_array($types)) {
            $this->import_modes = $types;
        }
    }

    /**
     * @param string $mode
     * @return bool
     */
    protected function isImportModeEnabled(string $mode)
    {
        return in_array($mode, $this->import_modes);
    }

    /**
     * Import selected types
     * @throws \ilVedaImporterLockedException
     * @throws \ilVedaConnectionException
     */
    public function import()
    {
        if ($this->settings->isLocked()) {
            throw new \ilVedaImporterLockedException(
                $this->plugin->txt('error_import_locked')
            );
        }

        $this->logger->info('Settings import lock');
        $this->settings->enableLock(true);
        $this->settings->save();

        try {
            if (
                ($this->getImportType() === self::IMPORT_TYPE_UNDEFINED && $this->settings->isSifaActive()) ||
                $this->getImportType() === self::IMPORT_TYPE_SIFA
            ) {
                $this->ensureClaimingPluginConfigured();
            }
            if ($this->isImportModeEnabled(self::IMPORT_USR)) {
                $this->importUsers();
            }
            if ($this->isImportModeEnabled(self::IMPORT_CRS)) {
                $this->importCourses();
            }
            if ($this->isImportModeEnabled(self::IMPORT_MEM)) {
                $this->importMembers();
            }
        } catch (ilVedaConnectionException $e) {
            throw $e;
        }

        // no error release lock
        $this->logger->info('Releasing import lock');
        $this->settings->enableLock(false);
        $this->settings->save();
    }

    /**
     * @throws \ilVedaConnectionException
     */
    protected function importUsers()
    {
        try {
            $connector = \ilVedaConnector::getInstance();
            $participants = $connector->getParticipants();
            $this->logger->dump($participants, \ilLogLevel::DEBUG);

            \ilVedaUserStatus::deleteDeprecated($participants);

            $importer = new \ilVedaUserImportAdapter($participants);
            $importer->import();

        } catch (ilVedaConnectionException $e) {
            throw $e;
        } catch (ilVedaUserImporterException $e) {
            throw $e;
        }
    }

    /**
     * @return bool
     */
    protected function importCourses()
    {
        try {
            if (
                $this->getImportType() === self::IMPORT_TYPE_SIBE ||
                (
                    $this->getImportType() === self::IMPORT_TYPE_UNDEFINED && $this->settings->isSibeActive()
                )
            ) {
                $importer = new \ilVedaCourseSibeImportAdapter();
                $importer->import();
            }
            if (
                $this->getImportType() === self::IMPORT_TYPE_SIFA ||
                (
                    $this->getImportType() === self::IMPORT_TYPE_UNDEFINED && $this->settings->isSifaActive()
                )
            ) {
                $importer = new ilVedaCourseImportAdapter();
                $importer->import();
            }
        } catch (\ilVedaConnectionException $e) {
            throw $e;
        } catch (\ilVedaCourseImporterException $e) {
            throw $e;
        }
        return true;
    }

    /**
     * @throws \ilVedaClaimingMissingException
     */
    protected function ensureClaimingPluginConfigured()
    {
        if (!$this->plugin->isClaimingPluginAvailable()) {
            throw new \ilVedaClaimingMissingException('', \ilVedaClaimingMissingException::ERR_MISSING);
        }
        if (!$this->plugin->isUDFClaimingPluginAvailable()) {
            throw new \ilVedaClaimingMissingException('', \ilVedaClaimingMissingException::ERR_MISSING_UDF);
        }
    }

    /**
     * Import membership assignments
     */
    protected function importMembers()
    {
        try {
            if (
                $this->getImportType() === self::IMPORT_TYPE_SIFA ||
                (
                    $this->getImportType() === self::IMPORT_TYPE_UNDEFINED && $this->settings->isSifaActive()
                )
            ) {
                $importer = new ilVedaMemberImportAdapter();
                $importer->import();
            }
            if (
                $this->getImportType() === self::IMPORT_TYPE_SIBE ||
                (
                    $this->getImportType() === self::IMPORT_TYPE_UNDEFINED && $this->settings->isSibeActive()
                )
            ) {
                $importer = new ilVedaMemberSibeImportAdapter();
                $importer->import();
            }
        } catch (\ilVedaConnectionException $e) {
            throw $e;
        }
    }

    public function handleCloningFailed()
    {
        $failed = \ilVedaCourseStatus::getProbablyFailed();
        foreach ($failed as $fail) {
            $this->logger->notice('Handling failed clone event for oid: ' . $fail->getOid());
            $connector = \ilVedaConnector::getInstance();
            try {
                if ($fail->getType() == \ilVedaCourseStatus::TYPE_SIFA) {
                    $connector->sendCourseCreationFailed($fail->getOid());
                } elseif ($fail->getType() == \ilVedaCourseStatus::TYPE_SIBE) {
                    $connector->sendSibeCourseCreationFailed(
                        $fail->getOid(),
                        \ilVedaConnector::COURSE_CREATION_FAILED_ELARNING
                    );
                } else {
                    $this->logger->error('Unknown type given for oid ' . $fail->getOid());
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                // no fallback
                continue;
            }
            // Fallback
            $status = new ilVedaCourseStatus($fail->getOid());
            $status->setModified(time());
            $status->setCreationStatus(\ilVedaCourseStatus::STATUS_FAILED);
            $status->save();
        }
    }

}