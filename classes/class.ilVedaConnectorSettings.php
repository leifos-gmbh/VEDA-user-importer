<?php

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorSettings
{
    public const SIFA_SETTINGS_TYPE = 1;
    public const SIBE_SETTINGS_TYPE = 2;


	public const HEADER_TOKEN = 'x-jwp-apiaccesstoken';

	private static $instance = null;

	private $storage = null;
	private $lock = false;
	private $cron_interval = 1;
	private $cron_last_execution = 0;

	private $restUser;
	private $restUrl;
	private $restPassword;

	/**
	 * @var bool
	 */
	private $active = false;

    private  $sifa_active = false;


    private $sibe_active = false;

	/**
	 * @var int
	 */
	private $loglevel = \ilLogLevel::OFF;

	/**
	 * @var string
	 */
	private $logfile = '';

	/**
	 * @var string
	 */
	private $authentication_token = '';

	/**
	 * @var string
	 */
	private $platform_id = '';

	/**
	 * @var string
	 */
	private $training_course = '';

	/**
	 * @var int
	 */
	private $sifa_participant_role = 0;


    /**
     * @var int
     */
    private $sibe_participant_role = 0;

	/**
	 * @var int
	 */
	private $sifa_import_ref_id = 0;

    /**
     * @var int
     */
    private $sibe_import_ref_id = 0;

	/**
	 * @var int
	 */
	private $switch_permanent_role = 0;

	/**
	 * @var int
	 */
	private $switch_temporary_role = 0;

    /**
     * @var bool
     */
    private $add_header_auth = false;

    /**
     * @var string
     */
    private $add_header_name = '';

    /**
     * @var string
     */
    private $add_header_value = '';

	/**
	 * ilVedaConnectorSettings constructor.
	 */
	public function __construct()
	{
		//db table settings column module
		$this->storage = new \ilSetting('vedaimp');
		$this->read();
	}

	/**
	 * @return \ilVedaConnectorSettings
	 */
	public static function getInstance(): ilVedaConnectorSettings
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new self();
	}

	/**
	 * @return bool
	 */
	public function hasSettingsForConnectionTest()
	{
		return
			strlen($this->getRestUrl()) &&
			strlen($this->getAuthenticationToken()) &&
			strlen($this->getPlatformId());
	}

	/**
	 * Read settings
	 */
	protected function read(): void
	{
		$this->enableLock($this->getStorage()->get('lock',$this->isLocked()));

		$this->cron_last_execution = $this->getStorage()->get('cron_last_execution',0);
		$this->cron_interval = $this->getStorage()->get('cron_interval',$this->cron_interval);

		$this->setRestUrl($this->getStorage()->get('resturl',$this->getRestUrl()));
		$this->setRestUser($this->getStorage()->get('restuser',$this->getRestUser()));
		$this->setRestPassword($this->getStorage()->get('restpassword', $this->getRestPassword()));
		$this->setAuthenticationToken($this->getStorage()->get('resttoken', $this->getAuthenticationToken()));
		$this->setPlatformId($this->getStorage()->get('platform_id', $this->getPlatformId()));
		$this->setTrainingCourse($this->getStorage()->get('training_course', $this->getTrainingCourse()));

		$this->setActive((bool) $this->getStorage()->get('active', $this->isActive()));
		$this->setSibeActive((bool) $this->getStorage()->get('sibe_active', $this->isSibeActive()));
        $this->setSifaActive((bool) $this->getStorage()->get('sifa_active', $this->isSifaActive()));
		$this->setLogLevel($this->getStorage()->get('loglevel', $this->getLogLevel()));
		$this->setLogFile($this->getStorage()->get('logfile', $this->getLogFile()));
		$this->setSifaParticipantRole($this->getStorage()->get('part_role', $this->getSifaParticipantRole()));
        $this->setSibeParticipantRole($this->getStorage()->get('sibe_part_role', $this->getSibeParticipantRole()));
		$this->setSifaImportDirectory($this->getStorage()->get('sifa_import_ref_id', $this->getSifaImportDirectory()));
        $this->setSibeImportDirectory($this->getStorage()->get('sibe_import_ref_id', $this->getSibeImportDirectory()));
		$this->setTemporarySwitchRole($this->getStorage()->get('switch_temporary_role', $this->getTemporarySwitchRole()));
		$this->setPermanentSwitchRole($this->getStorage()->get('switch_permanent_role', $this->getPermanentSwitchRole()));

		$this->setAddHeaderAuth($this->getStorage()->get('add_header_auth', $this->isAddHeaderAuthEnabled()));
        $this->setAddHeaderName($this->getStorage()->get('add_header_name', $this->getAddHeaderName()));
        $this->setAddHeaderValue($this->getStorage()->get('add_header_value', $this->getAddHeaderValue()));
	}

	/**
	 * @param int $role_id
	 */
	public function setPermanentSwitchRole(int $role_id)
	{
		$this->switch_permanent_role = $role_id;
	}

	/**
	 * @return int
	 */
	public function getPermanentSwitchRole() : int
	{
		return $this->switch_permanent_role;
	}

	/**
	 * @param int $role_id
	 */
	public function setTemporarySwitchRole(int $role_id)
	{
		$this->switch_temporary_role = $role_id;
	}

	/**
	 * @return int
	 */
	public function getTemporarySwitchRole() : int
	{
		return $this->switch_temporary_role;
	}

	/**
	 * @param bool $a_lock
	 */
	public function enableLock(bool $a_lock): void
	{
		$this->lock = $a_lock;
	}

	/**
	 * @return \ilSetting
	 */
	protected function getStorage(): ilSetting
	{
		return $this->storage;
	}

	/**
	 * @return bool
	 */
	public function isLocked(): bool
	{
		return $this->lock;
	}

    /**
     * @return bool
     */
    public function isSifaActive() : bool
    {
        return $this->sifa_active;
    }

    /**
     * @param bool $sifa_active
     */
    public function setSifaActive(bool $sifa_active) : void
    {
        $this->sifa_active = $sifa_active;
    }

    /**
     * @return bool
     */
    public function isSibeActive() : bool
    {
        return $this->sibe_active;
    }

    /**
     * @param bool $sibe_active
     */
    public function setSibeActive(bool $sibe_active) : void
    {
        $this->sibe_active = $sibe_active;
    }


	/**
	 * Save settings
	 */
	public function save(): void
	{
		$this->getStorage()->set('lock',(int) $this->isLocked());
		$this->getStorage()->set('cron_interval',$this->getCronInterval());

		$this->getStorage()->set('restpassword',$this->getRestPassword());
		$this->getStorage()->set('restuser',$this->getRestUser());
		$this->getStorage()->set('resturl', $this->getRestUrl());
		$this->getStorage()->set('resttoken', $this->getAuthenticationToken());
		$this->getStorage()->set('platform_id', $this->getPlatformId());
		$this->getStorage()->set('training_course', $this->getTrainingCourse());
		$this->getStorage()->set('add_header_auth', $this->isAddHeaderAuthEnabled());
        $this->getStorage()->set('add_header_name', $this->getAddHeaderName());
        $this->getStorage()->set('add_header_value', $this->getAddHeaderValue());

		$this->getStorage()->set('active', (int) $this->isActive());
		$this->getStorage()->set('sifa_active', (int) $this->isSifaActive());
        $this->getStorage()->set('sibe_active', (int) $this->isSibeActive());
		$this->getStorage()->set('loglevel', $this->getLogLevel());
		$this->getStorage()->set('logfile', $this->getLogFile());
		$this->getStorage()->set('part_role', $this->getSifaParticipantRole());
        $this->getStorage()->set('sibe_part_role', $this->getSibeParticipantRole());
		$this->getStorage()->set('sifa_import_ref_id', $this->getSifaImportDirectory());
        $this->getStorage()->set('sibe_import_ref_id', $this->getSibeImportDirectory());
		$this->getStorage()->set('switch_temporary_role', $this->getTemporarySwitchRole());
		$this->getStorage()->set('switch_permanent_role', $this->getPermanentSwitchRole());
	}

	/**
	 * @param int $a_int
	 */
	public function setCronInterval(int $a_int)
	{
		$this->cron_interval = $a_int;
	}

	/**
	 * @return int
	 */
	public function getCronInterval(): int
	{
		return $this->cron_interval;
	}

	/**
	 *
	 */
	public function updateLastCronExecution(): void
	{
		$this->getStorage()->set('cron_last_execution',time());
	}

	/**
	 * @param string|null $a_user
	 */
	public function setRestUser(?string $a_user): void
	{
		$this->restUser = $a_user;
	}

	/**
	 * @return string|null
	 */
	public function getRestUser(): ?string
	{
		return $this->restUser;
	}

	/**
	 * @param string|null $a_rest_url
	 */
	public function setRestUrl(?string $a_rest_url): void
	{
		$this->restUrl = $a_rest_url;
	}

	/**
	 * @return string|null
	 */
	public function getRestUrl(): ?string
	{
		return $this->restUrl;
	}

	/**
	 * @param string|null $a_pass
	 */
	public function setRestPassword(?string $a_pass): void
	{
		$this->restPassword = $a_pass;
	}

	/**
	 * @return string|null
	 */
	public function getRestPassword(): ?string
	{
		return $this->restPassword;
	}

	/**
	 * @param string|null $token
	 */
	public function setAuthenticationToken(?string $token)
	{
		$this->authentication_token = $token;
	}


	/**
	 * @return string|null
	 */
	public function getAuthenticationToken() : ?string
	{
		return $this->authentication_token;
	}

	/**
	 * @param string|null $id
	 */
	public function setPlatformId(?string $id)
	{
		$this->platform_id = $id;
	}

	/**
	 * @return string|null
	 */
	public function getPlatformId() : ?string
	{
		return $this->platform_id;
	}

	/**
	 * @param bool $active
	 */
	public function setActive(bool $active)
	{
		$this->active = $active;
	}

	/**
	 * @return bool
	 */
	public function isActive() : bool
	{
		return $this->active;
	}

	/**
	 * @param int $loglevel
	 */
	public function setLogLevel(int $loglevel)
	{
		$this->loglevel = $loglevel;
	}

	/**
	 * @return int
	 */
	public function getLogLevel() : int
	{
		return $this->loglevel;
	}

	/**
	 * @param string $file
	 */
	public function setLogFile(string $file)
	{
		$this->logfile = $file;
	}

	/**
	 * @return string
	 */
	public function getLogFile() : string
	{
		return $this->logfile;
	}


    /**
     * @param int $type
     * @return int|null
     * @throws InvalidArgumentException
     */
	public function getParticipantRoleByType(int $type) : ?int
    {
        switch ($type) {
            case self::SIFA_SETTINGS_TYPE:
                return $this->getSifaParticipantRole();
            case self::SIBE_SETTINGS_TYPE:
                return $this->getSibeParticipantsRole();
        }
        throw new  \InvalidArgumentException('Invalid type given');
    }

    /**
     * @return int
     */
    public function getSifaParticipantRole() : int
    {
        return $this->sifa_participant_role;
    }

    /**
     * @param int $sifa_participant_role
     */
    public function setSifaParticipantRole(int $sifa_participant_role) : void
    {
        $this->sifa_participant_role = $sifa_participant_role;
    }

    /**
     * @return int
     */
    public function getSibeParticipantRole() : int
    {
        return $this->sibe_participant_role;
    }

    /**
     * @param int $sibe_participant_role
     */
    public function setSibeParticipantRole(int $sibe_participant_role) : void
    {
        $this->sibe_participant_role = $sibe_participant_role;
    }

    /**
     * @param int $type
     * @return int
     * @throws \InvalidArgumentException
     */
    public function getImportDirectoryByType(int $type) : int
    {
        switch ($type) {
            case self::SIFA_SETTINGS_TYPE:
                return $this->getSifaImportDirectory();
            case self::SIBE_SETTINGS_TYPE:
                return $this->getSibeImportDirectory();
        }
        throw new  \InvalidArgumentException('Invalid type given');
    }

	/**
	 * @return int
	 */
	public function getSifaImportDirectory() : int
	{
		return $this->sifa_import_ref_id;
	}

	/**
	 * @param int $ref_id
	 */
	public function setSifaImportDirectory(int $ref_id)
	{
		$this->sifa_import_ref_id = $ref_id;
	}

    /**
     * @return int
     */
    public function getSibeImportDirectory() : int
    {
        return $this->sibe_import_ref_id;
    }

    /**
     * @param int $ref_id
     */
    public function setSibeImportDirectory(int $ref_id)
    {
        $this->sibe_import_ref_id = $ref_id;
    }

	/**
	 * @return string
	 */
	public function getTrainingCourse(): string
	{
		return $this->training_course;
	}

	/**
	 * @param string $training_course
	 */
	public function setTrainingCourse(string $training_course): void
	{
		$this->training_course = $training_course;
	}

    /**
     * @return bool
     */
    public function isAddHeaderAuthEnabled() : bool
    {
        return $this->add_header_auth;
    }

    /**
     * @param bool $add_header_auth
     */
    public function setAddHeaderAuth(bool $add_header_auth) : void
    {
        $this->add_header_auth = $add_header_auth;
    }

    /**
     * @return string
     */
    public function getAddHeaderName() : string
    {
        return $this->add_header_name;
    }

    /**
     * @param string $add_header_name
     */
    public function setAddHeaderName(string $add_header_name) : void
    {
        $this->add_header_name = $add_header_name;
    }

    /**
     * @return string
     */
    public function getAddHeaderValue() : string
    {
        return $this->add_header_value;
    }

    /**
     * @param string $add_header_value
     */
    public function setAddHeaderValue(string $add_header_value) : void
    {
        $this->add_header_value = $add_header_value;
    }
}
