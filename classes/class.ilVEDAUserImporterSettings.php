<?php

/**
 * @author Jesus Lopez <lopez@leifos.com>
 */
class ilVEDAUserImporterSettings
{
	private static $instance = null;

	private $storage = null;
	private $lock = false;
	private $cron_interval = 5;
	private $cron_last_execution = 0;

	private $restUser;
	private $restUrl;
	private $restPassword;

	public function __construct()
	{
		//db table settings column module
		$this->storage = new ilSetting('vedauserimporter_config');
		$this->read();
	}

	public static function getInstance(): ilVEDAUserImporterSettings
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilVEDAUserImporterSettings();
	}

	protected function read(): void
	{
		$this->enableLock($this->getStorage()->get('lock',$this->isLocked()));

		$this->cron_last_execution = $this->getStorage()->get('cron_last_execution',0);
		$this->cron_interval = $this->getStorage()->get('cron_interval',$this->cron_interval);

		$this->setRestUrl($this->getStorage()->get('resturl',$this->getRestUrl()));
		$this->setRestUser($this->getStorage()->get('restuser',$this->getRestUser()));
		$this->setRestPassword($this->getStorage()->get('restpassword', $this->getRestPassword()));
	}

	public function enableLock(bool $a_lock): void
	{
		$this->lock = $a_lock;
	}

	public function getStorage(): ilSetting
	{
		return $this->storage;
	}

	public function isLocked(): bool
	{
		return $this->lock;
	}

	public function save(): void
	{
		//db table settings columns lock, cron_interval
		$this->getStorage()->set('lock',(int) $this->isLocked());
		$this->getStorage()->set('cron_interval',$this->getCronInterval());

		//TODO --> DO NOT STORE THE PASSWORD AS PLAIN TEXT!!!!
		$this->getStorage()->set('restpassword',$this->getRestPassword());
		$this->getStorage()->set('restuser',$this->getRestUser());
		$this->getStorage()->set('resturl', $this->getRestUrl());
	}

	public function setCronInterval(int $a_int)
	{
		$this->cron_interval = $a_int;
	}

	public function getCronInterval(): int
	{
		return $this->cron_interval;
	}

	public function updateLastCronExecution(): void
	{
		$this->getStorage()->set('cron_last_execution',time());
	}

	public function setRestUser(?string $a_user): void
	{
		$this->restUser = $a_user;
	}

	public function getRestUser(): ?string
	{
		return $this->restUser;
	}

	public function setRestUrl(?string $a_rest_url): void
	{
		$this->restUrl = $a_rest_url;
	}

	public function getRestUrl(): ?string
	{
		return $this->restUrl;
	}

	public function setRestPassword(?string $a_pass): void
	{
		$this->restPassword = $a_pass;
	}

	public function getRestPassword(): ?string
	{
		return $this->restPassword;
	}
}
