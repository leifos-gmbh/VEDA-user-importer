<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilVedaUserStatus
 */
class ilVedaUserStatus
{
	public const STATUS_NONE = 0;
	public const STATUS_PENDING = 1;
	public const STATUS_SYNCHRONIZED = 2;

	private const TABLE_NAME = 'cron_crnhk_vedaimp_us';
	/**
	 * @var string
	 */
	private $oid = '';

	/**
	 * @var string
	 */
	private $login = '';

	/**
	 * @var int
	 */
	private $status_pwd  = self::STATUS_NONE;

	/**
	 * @var int
	 */
	private $status_created =  self::STATUS_NONE;

	/**
	 * @var bool
	 */
	private $import_failure = false;


	/**
	 * @var bool
	 */
	private $is_persistent = false;

	/**
	 * @var null | \ilDBInterface
	 */
	private $db = null;

	/**
	 * @var null | \ilLogger
	 */
	private $logger = null;



	/**
	 * ilVedaUserStatus constructor.
	 */
	public function __construct(string $oid = '')
	{
		global $DIC;

		$this->oid = $oid;

		$this->db = $DIC->database();
		$this->logger = $DIC->logger()->vedaimp();

		$this->read();
	}

	/**
	 * @return \ilVedaUserStatus[]
	 */
	public static  function getUsersWithPendingCreationStatus()
	{
		global $DIC;

		$db = $DIC->database();
		$logger = $DIC->logger()->vedaimp();

		$query = 'select oid from ' . self::TABLE_NAME . ' ' .
			'where status_created = ' . $db->quote(self::STATUS_PENDING, 'integer') . ' ' .
			'and import_failure = ' . $db->quote(0 , 'integer');
		$res = $db->query($query);
		$logger->dump($query);

		$pending_participants = [];
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {

			$pending_participants[] = new \ilVedaUserStatus($row->oid);
		}
		return $pending_participants;
	}

	/**
	 * @param int $usr_id
	 */
	public static function handleDeleteAccount(int $usr_id)
	{
		global $DIC;

		$logger = $DIC->logger()->vedaimp();

		$logger->debug('Handle delete account.');

		$import_id = \ilObjUser::_lookupImportId($usr_id);
		if(!$import_id) {
			$logger->debug('No veda user. Event ignored');
			return;
		}
		$status = new \ilVedaUserStatus($import_id);
		$status->delete();
	}

	/**
	 * Get all users
	 * @return \ilVedaUserStatus[]
	 * @throws \ilDatabaseException
	 */
	public static function getAllUsers()
	{
		global $DIC;

		$db = $DIC->database();
		$query = 'select oid from ' . self::TABLE_NAME;
		$res = $db->query($query);

		$all_users = [];
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
			$all_users[] = new \ilVedaUserStatus($row->oid);
		}
		return $all_users;
	}

	/**
	 * @param string $oid
	 */
	public function setOid(string $oid)
	{
		$this->oid = $oid;
	}

	/**
	 * @return string
	 */
	public function getOid() : string
	{
		return $this->oid;
	}

	/**
	 * @param \Swagger\Client\Model\TeilnehmerELearningPlattform[]
	 * @throws \ilDatabaseException
	 */
	public static function deleteDeprecated(array $participants)
	{
		foreach(self::getAllUsers() as $user) {
			$found_remote = false;
			foreach($participants as $participant) {
				if($user->getOid() == $participant->getTeilnehmer()->getOid()) {
					$found_remote = true;
				}
			}
			if(!$found_remote) {
				$user->delete();
			}
		}
	}

	/**
	 * @param string $login
	 */
	public function setLogin(string $login)
	{
		$this->login = $login;
	}

	/**
	 * @return string
	 */
	public function getLogin() : string
	{
		return $this->login;
	}

	/**
	 * @param int $status
	 */
	public function setPasswordStatus(int $status)
	{
		$this->status_pwd = $status;
	}

	/**
	 * @return int
	 */
	public function getPasswordStatus() : int
	{
		return $this->status_pwd;
	}

	/**
	 * @param int $status
	 */
	public function setCreationStatus(int $status)
	{
		$this->status_created = $status;
	}

	/**
	 * @return int
	 */
	public function getCreationStatus() : int
	{
		return $this->status_created;
	}

	/**
	 * @param bool $status
	 */
	public function setImportFailure(bool $status)
	{
		$this->import_failure = $status;
	}

	/**
	 * @return bool
	 */
	public function isImportFailure() : bool
	{
		return $this->import_failure;
	}

	/**
	 * @return mixed
	 */
	public function save()
	{
		if($this->is_persistent) {
			$this->update();
			return;
		}

		$query = 'insert into ' . self::TABLE_NAME . ' ' .
			'(oid, login, status_pwd, status_created, import_failure ) ' .
			'values ( '.
			$this->db->quote($this->getOid(),'text') . ', '.
			$this->db->quote($this->getLogin(),'text'). ', '.
			$this->db->quote($this->getPasswordStatus(),'integer'). ', '.
			$this->db->quote($this->getCreationStatus(), 'integer') . ', ' .
			$this->db->quote($this->isImportFailure(), 'integer') . ' ) ';
		$this->db->manipulate($query);
		$this->logger->debug($query);
		$this->is_persistent = true;
	}

	/**
	 * @return void
	 */
	protected function update()
	{
		$query = 'update ' . self::TABLE_NAME . ' '.
			'set ' .
			'login = ' . $this->db->quote($this->getLogin(),'text') . ', ' .
			'status_pwd = ' . $this->db->quote($this->getPasswordStatus(),'integer') . ', ' .
			'status_created = ' . $this->db->quote($this->getCreationStatus(),'integer') . ', ' .
			'import_failure = ' . $this->db->quote($this->isImportFailure(), 'integer') . ' ' .
			'where oid = ' . $this->db->quote($this->getOid(),'text');
		$this->logger->debug($query);
		$this->db->manipulate($query);
	}

	/**
	 * Delete entry
	 */
	public function delete()
	{
		$query = 'delete from ' . self::TABLE_NAME . ' ' .
			'where oid = ' . $this->db->quote($this->getOid(),'text');
		$this->db->manipulate($query);
 	}

	/**
	 * Read form db
	 * @throws \ilDatabaseException
	 */
	protected function read()
	{
		if(!$this->oid) {
			$this->is_persistent = false;
			return;
		}

		$query = 'select * from ' . self::TABLE_NAME . ' '.
			'where oid = ' . $this->db->quote($this->getOid(),'text');
		$res = $this->db->query($query);
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {

			$this->is_persistent = true;
			$this->setLogin($row->login);
			$this->setPasswordStatus((int) $row->status_pwd);
			$this->setCreationStatus((int) $row->status_created);
			$this->setImportFailure((bool) $row->import_failure);
		}
	}



}