<?php

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorSettings
{
    public const HEADER_TOKEN = 'x-jwp-apiaccesstoken';

    private const SETTINGS = 'vedaimp';
    private const SETTING_PERMANENT_SWITCH_ROLE = 'switch_permanent_role';
    private const SETTING_TEMPORARY_SWITCH_ROLE = 'switch_temporary_role';
    private const SETTING_LOCK = 'lock';
    private const SETTING_MAIL_ACTIVE = 'mail_active';
    private const SETTING_MAIL_TARGETS = 'mail_targets';
    private const SETTING_SIFA_ACTIVE = 'sifa_active';
    private const SETTING_STANDARD_ACTIVE = 'sibe_active';
    private const SETTING_CRON_INTERVALL = 'cron_interval';
    private const SETTING_CRON_LAST_EXECUTION = 'cron_last_execution';
    private const SETTING_REST_USER = 'restuser';
    private const SETTING_REST_URL = 'resturl';
    private const SETTING_REST_PASSWORD = 'restpassword';
    private const SETTING_REST_TOKEN = 'resttoken';
    private const SETTING_PLATTFORM_ID = 'platform_id';
    private const SETTING_ACTIVE = 'active';
    private const SETTING_LOGLEVEL = 'loglevel';
    private const SETTING_LOGIFLE = 'logfile';
    private const SETTING_PART_ROLE = 'part_role';
    private const SETTING_STANDARD_PART_ROLE = 'sibe_part_role';
    private const SETTING_SIFA_IMPORT_REF_ID = 'sifa_import_ref_id';
    private const SETTING_STANDARD_IMPORT_REF_ID = 'sibe_import_ref_id';
    private const SETTING_TRAINING_COURSE = 'training_course';
    private const SETTING_ADD_HEADER_AUTH = 'add_header_auth';
    private const SETTING_ADD_HEADER_NAME = 'add_header_name';
    private const SETTING_ADD_HEADER_VALUE = 'add_header_value';

    private ilSetting $storage;

    public function __construct()
    {
        $this->storage = new ilSetting(self::SETTINGS);
    }

    public static function getInstance() : ilVedaConnectorSettings
    {
        return new self();
    }

    public function hasSettingsForConnectionTest() : bool
    {
        return
            strlen($this->getRestUrl()) &&
            strlen($this->getAuthenticationToken()) &&
            strlen($this->getPlatformId());
    }

    public function setPermanentSwitchRole(int $role_id) : void
    {
        $this->storage->set(self::SETTING_PERMANENT_SWITCH_ROLE, $role_id);
    }

    public function getPermanentSwitchRole() : int
    {
        return (int) $this->storage->get(self::SETTING_PERMANENT_SWITCH_ROLE, 0);
    }

    public function setTemporarySwitchRole(int $role_id) : void
    {
        $this->storage->set(self::SETTING_TEMPORARY_SWITCH_ROLE, $role_id);
    }

    public function getTemporarySwitchRole() : int
    {
        return $this->storage->get(self::SETTING_TEMPORARY_SWITCH_ROLE, 0);
    }

    public function enableLock(bool $a_lock) : void
    {
        $this->storage->set(self::SETTING_LOCK, $a_lock);
    }

    public function isLocked() : bool
    {
        return (bool) $this->storage->get(self::SETTING_LOCK, false);
    }

    protected function getStorage() : ilSetting
    {
        return $this->storage;
    }

    public function isMailActive() : bool
    {
        return (bool) $this->storage->get(self::SETTING_MAIL_ACTIVE, false);
    }

    public function setMailActive(bool $value) : void
    {
        $this->storage->set(self::SETTING_MAIL_ACTIVE, $value);
    }

    public function getMailTargets() : string
    {
        return $this->storage->get(self::SETTING_MAIL_TARGETS, '');
    }

    public function setMailTargets(string $targets) : void
    {
        $this->storage->set(self::SETTING_MAIL_TARGETS, $targets);
    }

    public function isSifaActive() : bool
    {
        return (bool) $this->storage->get(self::SETTING_SIFA_ACTIVE, false);
    }

    public function setSifaActive(bool $sifa_active) : void
    {
        $this->storage->set(self::SETTING_SIFA_ACTIVE, $sifa_active);
    }

    public function isStandardActive() : bool
    {
        return (bool) $this->storage->get(self::SETTING_STANDARD_ACTIVE, false);
    }

    public function setStandardActive(bool $standard_active) : void
    {
        $this->storage->set(self::SETTING_STANDARD_ACTIVE, $standard_active);
    }

    public function setCronInterval(int $a_int) : void
    {
        $this->storage->set(self::SETTING_CRON_INTERVALL, $a_int);
    }

    public function getCronInterval() : int
    {
        return (int) $this->storage->get(self::SETTING_CRON_INTERVALL, 0);
    }

    public function updateLastCronExecution() : void
    {
        $this->getStorage()->set(self::SETTING_CRON_LAST_EXECUTION, time());
    }

    public function setRestUser(?string $a_user) : void
    {
        $this->storage->set(self::SETTING_REST_USER, $a_user);
    }

    public function getRestUser() : ?string
    {
        return $this->storage->get(self::SETTING_REST_USER);
    }

    public function setRestUrl(?string $a_rest_url) : void
    {
        $this->storage->set(self::SETTING_REST_URL, $a_rest_url);
    }

    public function getRestUrl() : ?string
    {
        return $this->storage->get(self::SETTING_REST_URL);
    }

    public function setRestPassword(?string $a_pass) : void
    {
        $this->storage->set(self::SETTING_REST_PASSWORD, $a_pass);
    }

    public function getRestPassword() : ?string
    {
        return $this->storage->get(self::SETTING_REST_PASSWORD);
    }

    public function setAuthenticationToken(?string $token) : void
    {
        $this->storage->set(self::SETTING_REST_TOKEN, $token);
    }

    public function getAuthenticationToken() : ?string
    {
        return $this->storage->get(self::SETTING_REST_TOKEN);
    }

    public function setPlatformId(?string $id) : void
    {
        $this->storage->set(self::SETTING_PLATTFORM_ID, $id);
    }

    public function getPlatformId() : string
    {
        return $this->storage->get(self::SETTING_PLATTFORM_ID, '');
    }

    public function setActive(bool $active) : void
    {
        $this->storage->set(self::SETTING_ACTIVE, $active);
    }

    public function isActive() : bool
    {
        return (bool) $this->storage->get(self::SETTING_ACTIVE, false);
    }

    public function setLogLevel(int $loglevel) : void
    {
        $this->storage->set(self::SETTING_LOGLEVEL, $loglevel);
    }

    public function getLogLevel() : int
    {
        return (int) $this->storage->get(self::SETTING_LOGLEVEL, ilLogLevel::OFF);
    }

    public function setLogFile(string $file) : void
    {
        $this->storage->set(self::SETTING_LOGIFLE, $file);
    }

    public function getLogFile() : string
    {
        return $this->storage->get(self::SETTING_LOGIFLE, '');
    }

    public function setSifaParticipantRole(int $sifa_participant_role) : void
    {
        $this->storage->set(self::SETTING_PART_ROLE, $sifa_participant_role);
    }

    public function getSifaParticipantRole() : int
    {
        return (int) $this->storage->get(self::SETTING_PART_ROLE, 0);
    }
    public function setStandardParticipantRole(int $standard_participant_role) : void
    {
        $this->storage->set(self::SETTING_STANDARD_PART_ROLE, $standard_participant_role);
    }

    public function getStandardParticipantRole() : int
    {
        return (int) $this->storage->get(self::SETTING_STANDARD_PART_ROLE, 0);
    }

    public function setSifaImportDirectory(int $ref_id) : void
    {
        $this->storage->set(self::SETTING_SIFA_IMPORT_REF_ID, $ref_id);
    }

    public function getSifaImportDirectory() : int
    {
        return (int) $this->storage->get(self::SETTING_SIFA_IMPORT_REF_ID, 0);
    }

    public function setStandardImportDirectory(int $ref_id) : void
    {
        $this->storage->set(self::SETTING_STANDARD_IMPORT_REF_ID, $ref_id);
    }

    public function getStandardImportDirectory() : int
    {
        return (int) $this->storage->get(self::SETTING_STANDARD_IMPORT_REF_ID, 0);
    }

    public function setTrainingCourse(string $training_course) : void
    {
        $this->storage->set(self::SETTING_TRAINING_COURSE, $training_course);
    }

    public function getTrainingCourse() : string
    {
        return $this->storage->get(self::SETTING_TRAINING_COURSE, '');
    }

    public function setAddHeaderAuth(bool $add_header_auth) : void
    {
        $this->storage->set(self::SETTING_ADD_HEADER_AUTH, $add_header_auth);
    }

    public function isAddHeaderAuthEnabled() : bool
    {
        return (bool) $this->storage->get(self::SETTING_ADD_HEADER_AUTH, false);
    }

    public function setAddHeaderName(string $add_header_name) : void
    {
        $this->storage->set(self::SETTING_ADD_HEADER_NAME, $add_header_name);
    }

    public function getAddHeaderName() : string
    {
        return $this->storage->get(self::SETTING_ADD_HEADER_NAME, '');
    }

    public function setAddHeaderValue(string $add_header_value) : void
    {
        $this->storage->set(self::SETTING_ADD_HEADER_VALUE, $add_header_value);
    }

    public function getAddHeaderValue() : string
    {
        return $this->storage->get(self::SETTING_ADD_HEADER_VALUE, '');
    }
}
