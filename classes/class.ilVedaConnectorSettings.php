<?php

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorSettings
{
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
	 * @var int
	 */
	private $participant_role = 0;

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

		$this->setActive((bool) $this->getStorage()->get('active', $this->isActive()));
		$this->setLogLevel($this->getStorage()->get('loglevel', $this->getLogLevel()));
		$this->setLogFile($this->getStorage()->get('logfile', $this->getLogFile()));
		$this->setParticipantRole($this->getStorage()->get('part_role', $this->getParticipantRole()));
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

		$this->getStorage()->set('active', (int) $this->isActive());
		$this->getStorage()->set('loglevel', $this->getLogLevel());
		$this->getStorage()->set('logfile', $this->getLogFile());
		$this->getStorage()->set('part_role', $this->getParticipantRole());
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
	 * @param int $role
	 */
	public function setParticipantRole(int $role)
	{
		$this->participant_role = $role;
	}

	/**
	 * @return int
	 */
	public function getParticipantRole() : int
	{
		return $this->participant_role;
	}
}
