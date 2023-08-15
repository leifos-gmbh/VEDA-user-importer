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
    protected int $import_type = self::IMPORT_TYPE_UNDEFINED;
    /**
     * @var string[]
     */
    protected $import_modes = [];

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

    public function setImportType(int $type) : void
    {
        $this->import_type = $type;
    }

    public function getImportType() : int
    {
        return $this->import_type;
    }

    public function setImportMode(bool $all, array $types = null) : void
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

    protected function isImportModeEnabled(string $mode) : bool
    {
        return in_array($mode, $this->import_modes);
    }

    /**
     * Import selected types
     * @throws ilVedaImporterLockedException
     * @throws ilVedaConnectionException
     */
    public function import() : void
    {
        if ($this->settings->isLocked()) {
            throw new ilVedaImporterLockedException(
                $this->plugin->txt('error_import_locked')
            );
        }

        $this->logger->info('Settings import lock');
        $this->settings->enableLock(true);

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

        // no error release lock
        $this->logger->info('Releasing import lock');
        $this->settings->enableLock(false);
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
     * @throws ilVedaConnectionException
     * @throws ilVedaCourseImporterException
     */
    protected function importCourses() : bool
    {
        if (
            $this->getImportType() === self::IMPORT_TYPE_STANDARD ||
            (
                $this->getImportType() === self::IMPORT_TYPE_UNDEFINED &&
                $this->settings->isStandardActive()
            )
        ) {
            $this->my_api->importStandardCourses();
        }
        if (
            $this->getImportType() === self::IMPORT_TYPE_SIFA ||
            (
                $this->getImportType() === self::IMPORT_TYPE_UNDEFINED &&
                $this->settings->isSifaActive()
            )
        ) {
            $this->my_api->importSIFACourses();
        }
        return true;
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

    /**
     * Import membership assignments
     * @throws ilVedaConnectionException
     */
    protected function importMembers() : void
    {
        if (
            $this->getImportType() === self::IMPORT_TYPE_SIFA ||
            (
                $this->getImportType() === self::IMPORT_TYPE_UNDEFINED &&
                $this->settings->isSifaActive()
            )
        ) {
            $this->my_api->importSIFAMembers();
        }
        if (
            $this->getImportType() === self::IMPORT_TYPE_STANDARD ||
            (
                $this->getImportType() === self::IMPORT_TYPE_UNDEFINED &&
                $this->settings->isStandardActive()
            )
        ) {
            $this->my_api->importStandardMembers();
        }
    }
}
