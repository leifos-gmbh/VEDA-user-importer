<?php

class ilVedaImporter
{
    /**
     * @var int
     */
    public const IMPORT_TYPE_UNDEFINED = 0;
    /**
     * @var int
     */
    public const IMPORT_TYPE_SIFA = 1;
    /**
     * @var int
     */
    public const IMPORT_TYPE_STANDARD = 2;
    /**
     * @var string
     */
    public const IMPORT_USR = 'usr';
    /**
     * @var string
     */
    public const IMPORT_CRS = 'crs';
    /**
     * @var string
     */
    public const IMPORT_MEM = 'mem';
    /**
     * @var int
     */
    public const IMPORT_NONE = 0;
    /**
     * @var int
     */
    public const IMPORT_ALL = 1;
    /**
     * @var int
     */
    public const IMPORT_SELECTED = 2;

    protected static ?ilVedaImporter $instance = null;
    protected ilLogger $logger;
    protected ilVedaConnectorSettings $settings;
    protected ilVedaConnectorPlugin $plugin;
    protected ilVedaApiInterface $my_api;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->vedaimp();
        $this->settings = ilVedaConnectorSettings::getInstance();
        $this->plugin = ilVedaConnectorPlugin::getInstance();
        $this->my_api = (new ilVedaApiFactory())->getVedaClientApi();
    }

    /**
     * @return ilVedaImporter
     */
    public static function getInstance() : ilVedaImporter
    {
        if (!self::$instance instanceof ilVedaImporter) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Import selected types
     * @throws ilVedaImporterLockedException
     * @throws ilVedaConnectionException
     */
    public function import(int $import_type, bool $all, array $types = []) : void
    {
        $modes = $this->getImportModes($all, $types);
        if ($this->settings->isLocked()) {
            throw new ilVedaImporterLockedException(
                $this->plugin->txt('error_import_locked')
            );
        }
        $this->logger->info('Settings import lock');
        $this->settings->enableLock(true);

        if (
            $import_type === ilVedaImporter::IMPORT_TYPE_SIFA ||
            (
                $import_type === self::IMPORT_TYPE_UNDEFINED &&
                $this->settings->isSifaActive()
            )
        ) {
            $this->logger->debug('SIFA import startet.');
            $this->importSifa($modes);
        }
        if (
            $import_type === ilVedaImporter::IMPORT_TYPE_STANDARD ||
            (
                $import_type === self::IMPORT_TYPE_UNDEFINED &&
                $this->settings->isStandardActive()
            )
        ) {
            $this->logger->debug('Standard import startet.');
            $this->importStandard($modes);
        }

        // no error release lock
        $this->logger->info('Releasing import lock');
        $this->settings->enableLock(false);
    }

    protected function importSifa(array $modes) : void
    {
        $this->ensureClaimingPluginConfigured();
        if ($this->isImportModeEnabled(self::IMPORT_USR, $modes)) {
            $this->logger->debug('Importing users');
            $this->my_api->deleteDeprecatedILIASUsers();
            $this->my_api->importILIASUsers();
        }
        if ($this->isImportModeEnabled(self::IMPORT_CRS, $modes)) {
            $this->logger->debug('Importing courses');
            $this->my_api->importSIFACourses();
        }
        if ($this->isImportModeEnabled(self::IMPORT_MEM, $modes)) {
            $this->logger->debug('Importing memberships');
            $this->my_api->importSIFAMembers();
        }
    }

    protected function importStandard(array $modes) : void
    {
        if ($this->isImportModeEnabled(self::IMPORT_USR, $modes)) {
            $this->logger->debug('Importing users');
            $this->my_api->deleteDeprecatedILIASUsers();
            $this->my_api->importILIASUsers();
        }
        if ($this->isImportModeEnabled(self::IMPORT_CRS, $modes)) {
            $this->logger->debug('Importing courses');
            $this->my_api->importStandardCourses();
        }
        if ($this->isImportModeEnabled(self::IMPORT_MEM, $modes)) {
            $this->logger->debug('Importing memeberships');
            $this->my_api->importStandardMembers();
        }
    }

    protected function getImportModes(bool $all, array $types = []) : array
    {
        return $all ?
            [
                self::IMPORT_USR,
                self::IMPORT_CRS,
                self::IMPORT_MEM,
            ]
            : $types;
    }

    protected function isImportModeEnabled(string $mode, array $modes) : bool
    {
        return in_array($mode, $modes);
    }

    /**
     * @throws ilVedaConnectionException
     * @throws ilVedaUserImporterException
     */
    protected function importUsers() : void
    {
        $this->my_api->deleteDeprecatedILIASUsers();
        $this->my_api->importILIASUsers();
    }

    /**
     * @throws ilVedaClaimingMissingException
     */
    protected function ensureClaimingPluginConfigured() : void
    {
        if (!$this->plugin->isClaimingPluginAvailable()) {
            throw new ilVedaClaimingMissingException('', ilVedaClaimingMissingException::ERR_MISSING);
        }
        if (!$this->plugin->isUDFClaimingPluginAvailable()) {
            throw new ilVedaClaimingMissingException('', ilVedaClaimingMissingException::ERR_MISSING_UDF);
        }
    }
}
