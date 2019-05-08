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
		$this->storage = new ilSetting('vedauserimporter_config');
		$this->read();
	}

	/**
	 * Get singleton instance
	 *
	 * @return ilVEDAUserImporterSettings
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilVEDAUserImporterSettings();
	}

	protected function read()
	{
		$this->enableLock($this->getStorage()->get('lock',$this->isLocked()));
		$this->cron_last_execution = $this->getStorage()->get('cron_last_execution',0);
	}

	public function enableLock($a_lock)
	{
		$this->lock = $a_lock;
	}

	public function getStorage()
	{
		return $this->storage;
	}

	public function isLocked()
	{
		return $this->lock;
	}

	public function save()
	{
		$this->getStorage()->set('lock',(int) $this->isLocked());
		$this->getStorage()->set('cron_interval',$this->getCronInterval());
	}

	public function setCronInterval($a_int)
	{
		$this->cron_interval = $a_int;
	}

	public function getCronInterval()
	{
		return $this->cron_interval;
	}

	public function updateLastCronExecution()
	{
		$this->getStorage()->set('cron_last_execution',time());
	}

	public function setRestUser($a_user)
	{
		$this->restUser = $a_user;
	}
	public function getRestUser()
	{
		return $this->restUser;
	}
	public function setRestUrl($a_rest_url)
	{
		$this->restUrl = $a_rest_url;
	}
	public function getRestUrl()
	{
		return $this->restUrl;
	}
	public function setRestPassword($a_pass)
	{
		$this->restPassword = $a_pass;
	}
	public function getRestPassword()
	{
		return $this->restPassword;
	}
}
