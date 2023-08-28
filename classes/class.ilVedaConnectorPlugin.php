<?php

use Monolog\Handler\StreamHandler;

/**
 * VEDA connector plugin base class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorPlugin extends \ilCronHookPlugin implements \ilAppEventListener
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

    private ?ilAdvancedMDClaimingPlugin $claiming = null;
    private ?ilVedaUDFClaimingPlugin $udfclaiming = null;
    private static ?ilVedaConnectorPlugin $instance = null;
    private ?ilLogger $logger = null;
    private ilPluginAdmin $plugin_admin;

    public const PNAME = 'VedaConnector';
    private const CTYPE = 'Services';
    private const CNAME = 'Cron';
    private const SLOT_ID = 'crnhk';

    /**
     * Claiming plugin
     */
    private const CLAIMING_CTYPE = 'Services';
    private const CLAIMING_CNAME = 'AdvancedMetaData';
    private const CLAIMING_SLOT_ID = 'amdc';
    private const CLAIMING_NAME = 'VedaMDClaiming';

    /**
     * Claiming plugin
     */
    private const CLAIMING_UDF_CTYPE = 'Services';
    private const CLAIMING_UDF_CNAME = 'User';
    private const CLAIMING_UDF_SLOT_ID = 'udfc';
    private const CLAIMING_UDF_NAME = 'VedaUDFClaiming';

    public function __construct()
    {
        global $DIC;
        $this->plugin_admin = $DIC['ilPluginAdmin'];
        $this->logger = $DIC->logger()->vedaimp();
        parent::__construct();
    }

    public static function getInstance() : ?ilVedaConnectorPlugin
    {
        if (!is_null(self::$instance)) {
            return self::$instance;
        }
        $plugin = ilPluginAdmin::getPluginObject(
            self::CTYPE,
            self::CNAME,
            self::SLOT_ID,
            self::PNAME
        );
        if ($plugin instanceof ilVedaConnectorPlugin) {
            return self::$instance = $plugin;
        }
        return null;
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

    public function getCronJobInstance($a_job_id) : ilVedaConnectorCronJob
    {
        return new ilVedaConnectorCronJob();
    }

    public function getLogger() : \ilLogger
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
        $files = array_diff(scandir($lib_path), ['.', '..']);
        $this->logger->info('start loading api classes');
        if (!is_array($files)) {
            $this->logger->info('lib folder does not exist');
            return;
        }
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

    protected function init() : void
    {
        //require($this->getDirectory() . '/vendor/autoload.php');
        $this->loadApiClasses();

        $settings = ilVedaConnectorSettings::getInstance();
        $this->logger->debug('Set log level to: ' . $settings->getLogLevel());

        if (
            $settings->getLogLevel() != \ilLogLevel::OFF &&
            $settings->getLogFile() != ''
        ) {
            $stream_handler = new StreamHandler(
                $settings->getLogFile(),
                $settings->getLogLevel(),
                true
            );
            $line_formatter = new ilLineFormatter(\ilLoggerFactory::DEFAULT_FORMAT, 'Y-m-d H:i:s.u', true, true);
            $stream_handler->setFormatter($line_formatter);
            $this->logger->getLogger()->pushHandler($stream_handler);
        }

        // format lines
        foreach ($this->logger->getLogger()->getHandlers() as $handler) {
            $handler->setLevel($settings->getLogLevel());
        }

        // init claiming plugin
        foreach ($this->plugin_admin->getActivePluginsForSlot(
            self::CLAIMING_CTYPE,
            self::CLAIMING_CNAME,
            self::CLAIMING_SLOT_ID
        ) as $plugin_name) {
            if ($plugin_name != self::CLAIMING_NAME) {
                continue;
            }
            $plugin = \ilPluginAdmin::getPluginObject(
                self::CLAIMING_CTYPE,
                self::CLAIMING_CNAME,
                self::CLAIMING_SLOT_ID,
                self::CLAIMING_NAME
            );
            if ($plugin instanceof ilAdvancedMDClaimingPlugin) {
                $this->claiming = $plugin;
            }
        }

        // init udf claiming plugin
        foreach ($this->plugin_admin->getActivePluginsForSlot(
            self::CLAIMING_UDF_CTYPE,
            self::CLAIMING_UDF_CNAME,
            self::CLAIMING_UDF_SLOT_ID
        ) as $plugin_name) {
            if ($plugin_name != self::CLAIMING_UDF_NAME) {
                continue;
            }
            $plugin = \ilPluginAdmin::getPluginObject(
                self::CLAIMING_UDF_CTYPE,
                self::CLAIMING_UDF_CNAME,
                self::CLAIMING_UDF_SLOT_ID,
                self::CLAIMING_UDF_NAME
            );
            $plugin = new ilVedaUDFClaimingPlugin();
            if ($plugin instanceof ilVedaUDFClaimingPlugin) {
                $this->udfclaiming = $plugin;
            }
        }
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
        return $this->claiming instanceof \ilVedaMDClaimingPlugin;
    }

    public function isUDFClaimingPluginAvailable() : bool
    {
        return $this->udfclaiming instanceof \ilVedaUDFClaimingPlugin;
    }

    /**
     * Handle an event in a listener.
     *
     * @param    string $a_component component, e.g. "Modules/Forum" or "Services/User"
     * @param    string $a_event event e.g. "createUser", "updateUser", "deleteUser", ...
     * @param    array $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
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
