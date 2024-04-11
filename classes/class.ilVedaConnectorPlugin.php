<?php

use Monolog\Handler\StreamHandler;

/**
 * VEDA connector plugin base class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorPlugin extends ilCronHookPlugin implements ilAppEventListener
{
    protected const COURSE_SERVICE = 'Modules/Course';
    protected const USER_SERVICE = 'Services/User';
    protected const OBJECT_SERVICE = 'Services/Object';
    protected const ACCESS_CONTROL_SERVICE = 'Services/AccessControl';
    protected const TRACKING_SERVICE = 'Services/Tracking';
    protected const EVENT_UPDATE_PASSWORD = 'passwordChanged';
    protected const EVENT_DELETE_USER = 'deleteUser';
    protected const EVENT_AFTER_CLONING = 'afterCloning';
    protected const EVENT_AFTER_CLONING_DEPENDENCIES = 'afterCloningDependencies';
    protected const EVENT_UPDATE_STATUS = 'updateStatus';
    protected const EVENT_PASSED_COURSE = 'participantHasPassedCourse';
    protected const EVENT_ADD_PARTICIPANT = 'addParticipant';
    protected const EVENT_ASSIGN_USER = 'assignUser';
    public const PNAME = 'VedaConnector';

    public const PLUGIN_ID = 'vedaimp';
    public const PLUGIN_ID_VEDA_MD_CLAIMING = "vedaclaiming";
    public const PLUGIN_ID_VEDA_UDF_CLAIMING = "vedaudfclaiming";

    protected ?ilAdvancedMDClaimingPlugin $claiming = null;
    protected ?ilVedaUDFClaimingPlugin $udfclaiming = null;
    protected static ?ilVedaConnectorPlugin $instance = null;
    protected ?ilLogger $logger = null;
    protected ilComponentFactory $component_factory;


    public function __construct(
        ilDBInterface $db,
        ilComponentRepositoryWrite $component_repository,
        string $id
    ) {
        global $DIC;
        $this->component_factory = $DIC['component.factory'];
        $this->logger = $DIC->logger()->vedaimp();
        parent::__construct($db, $component_repository, $id);
    }

    public static function getInstance(): ilVedaConnectorPlugin
    {
        global $DIC;
        if (isset(self::$instance)) {
            return self::$instance;
        }
        /** @var ilComponentFactory $component_factory */
        $component_factory = $DIC["component.factory"];
        /** @var ilVedaConnectorPlugin $plugin */
        $plugin = $component_factory->getPlugin(self::PLUGIN_ID);
        return $plugin;
    }

    public function getPluginName() : string
    {
        return self::PNAME;
    }

    /**
     * @return ilVedaConnectorCronJob[]
     */
    public function getCronJobInstances() : array
    {
        return [
            new ilVedaConnectorCronJob()
        ];
    }

    public function getCronJobInstance(string $jobId) : ilVedaConnectorCronJob
    {
        return new ilVedaConnectorCronJob();
    }

    public function getLogger() : ilLogger
    {
        return $this->logger;
    }

    protected function loadApiClasses() : void
    {
        /**
         * @var string[] $files
         * @var string[] $paths
         */
        $lib_path = __DIR__ . '/../' . 'lib';
        if (!scandir($lib_path)) {
            $this->logger->info('lib folder does not exist');
            return;
        }
        $files = array_diff(scandir($lib_path), ['.', '..']);
        $this->logger->info('start loading api classes');
        $paths = array_fill(0, count($files), $lib_path);
        while (count($files) > 0) {
            $current_file = array_shift($files);
            $current_path = array_shift($paths);
            $file_path = $current_path . '/' . $current_file;
            if (is_dir($file_path)) {
                $additional_files = array_diff(scandir($file_path), ['.', '..']);
                $additional_paths = array_fill(0, count($additional_files), $file_path);
                array_push($files, ...$additional_files);
                array_push($paths, ...$additional_paths);
                continue;
            }
            if (is_file($file_path) && str_ends_with($current_file, '.php')) {
                include_once $file_path;
            }
        }
    }

    protected function initVedaUdfClaimingPluginInstance(): void
    {
        /** @var ilVedaUDFClaimingPlugin $plugin */
        $plugin = $this->component_factory->getPlugin(self::PLUGIN_ID_VEDA_UDF_CLAIMING);
        $this->udfclaiming = $plugin;
    }

    protected function initVedaMDClaimingPluginInstance(): void
    {
        /** @var ilVedaMDClaimingPlugin $plugin */
        $plugin = $this->component_factory->getPlugin(self::PLUGIN_ID_VEDA_MD_CLAIMING);
        $this->claiming = $plugin;
    }

    protected function init() : void
    {
        $this->loadApiClasses();
        $settings = ilVedaConnectorSettings::getInstance();
        $this->logger->debug('Set log level to: ' . $settings->getLogLevel());

        if (
            $settings->getLogLevel() != ilLogLevel::OFF &&
            $settings->getLogFile() != ''
        ) {
            $stream_handler = new StreamHandler(
                $settings->getLogFile(),
                $settings->getLogLevel(),
                true
            );
            $default_format = "[%suid%] [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
            $line_formatter = new ilLineFormatter($default_format, 'Y-m-d H:i:s.u', true, true);
            $stream_handler->setFormatter($line_formatter);
            $this->logger->getLogger()->pushHandler($stream_handler);
        }

        // format lines
        foreach ($this->logger->getLogger()->getHandlers() as $handler) {
            $handler->setLevel($settings->getLogLevel());
        }

        $this->initVedaUdfClaimingPluginInstance();
        $this->initVedaMDClaimingPluginInstance();
    }

    public function getClaimingPlugin() : ?ilAdvancedMDClaimingPlugin
    {
        return $this->claiming;
    }

    public function getUDFClaimingPlugin() : ?ilVedaUDFClaimingPlugin
    {
        return $this->udfclaiming;
    }

    public function isClaimingPluginAvailable() : bool
    {
        return $this->claiming instanceof ilVedaMDClaimingPlugin;
    }

    public function isUDFClaimingPluginAvailable() : bool
    {
        return $this->udfclaiming instanceof ilVedaUDFClaimingPlugin;
    }

    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        $plugin = self::getInstance();
        $logger = $plugin->getLogger();
        $my_api = (new ilVedaApiFactory())->getVedaClientApi();
        $user_db_manager = (new ilVedaRepositoryFactory())->getUserRepository();

        $logger->info('Handling event : ' . $a_event . ' from ' . $a_component);

        if (
            $a_component == self::USER_SERVICE &&
            $a_event == self::EVENT_UPDATE_PASSWORD
        ) {
            $my_api->handlePasswordChanged($a_parameter['usr_id']);
        }
        if (
            $a_component == self::USER_SERVICE &&
            $a_event == self::EVENT_DELETE_USER
        ) {
            $user_db_manager->deleteUserByID($a_parameter['usr_id']);
        }
        if (
            $a_component == self::OBJECT_SERVICE &&
            $a_event == self::EVENT_AFTER_CLONING
        ) {
            $my_api->handleAfterCloningSIFAEvent(
                $a_parameter['source_id'],
                $a_parameter['target_id'],
                $a_parameter['copy_id']
            );
            $my_api->handleAfterCloningStandardEvent(
                $a_parameter['source_id'],
                $a_parameter['target_id'],
                $a_parameter['copy_id']
            );
        }
        if (
            $a_component == self::OBJECT_SERVICE &&
            $a_event == self::EVENT_AFTER_CLONING_DEPENDENCIES
        ) {
            $my_api->handleAfterCloningDependenciesSIFAEvent(
                $a_parameter['source_id'],
                $a_parameter['target_id'],
                $a_parameter['copy_id']
            );
            $my_api->handleAfterCloningDependenciesStandardEvent(
                $a_parameter['source_id'],
                $a_parameter['target_id'],
                $a_parameter['copy_id']
            );
        }
        if (
            $a_component == self::TRACKING_SERVICE &&
            $a_event == self::EVENT_UPDATE_STATUS
        ) {
            $my_api->handleTrackingEvent(
                $a_parameter['obj_id'],
                $a_parameter['usr_id'],
                $a_parameter['status']
            );
        }
        if (
            $a_component == self::OBJECT_SERVICE
        ) {
            $my_api->handleCloningFailed();
        }
    }
}
