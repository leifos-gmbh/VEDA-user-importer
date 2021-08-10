<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilVedaUserStatus
 */
class ilVedaCourseStatus
{
	public const STATUS_NONE = 0;
	public const STATUS_PENDING = 1;
	public const STATUS_SYNCHRONIZED = 2;
	public const STATUS_FAILED = 3;

	public const ASSUMPTION_FAILED_SECONDS = 5400;

	private const TABLE_NAME = 'cron_crnhk_vedaimp_crs';
	/**
	 * @var string
	 */
	private $oid = '';

	/**
	 * @var int
	 */
	private $obj_id = 0;


	/**
	 * @var int
	 */
	private $switch_permanent_role = 0;

	/**
	 * @var int
	 */
	private $switch_temporary_role = 0;

	/**
	 * @var int
	 */
	private $status_created =  self::STATUS_NONE;


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
     * @var int
     */
	private $modified = 0;




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
     * @return ilVedaCourseStatus[]
     * @throws ilDatabaseException
     */
	public static function getProbablyFailed() : array
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'select oid from ' . self::TABLE_NAME . ' ' .
            'where ' .
            'status_created = ' . $db->quote(self::STATUS_PENDING, ilDBConstants::T_INTEGER) . ' ' .
            'and modified < ' . $db->quote(time() - self::ASSUMPTION_FAILED_SECONDS, ilDBConstants::T_INTEGER);
        $res = $db->query($query);
        $failed = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $failed[] = new self($row->oid);
        }
        return $failed;
    }

	/**
	 * @return ilVedaCourseStatus[]
	 * @throws \ilDatabaseException
	 */
	public static function getAllCourses()
	{
		global $DIC;

		$db = $DIC->database();

		$query = 'select oid from ' . self::TABLE_NAME;
		$res = $db->query($query);

		$courses = [];
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
			$courses[] = new \ilVedaCourseStatus($row->oid);
		}
		return $courses;
	}

    /**
     * @return int
     */
    public function getModified() : int
    {
        return $this->modified ? $this->modified : time();
    }

    /**
     * @param int $modified
     */
    public function setModified(int $modified) : void
    {
        $this->modified = $modified;
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
	 * @param int $role
	 */
	public function setPermanentSwitchRole(int $role)
	{
		$this->switch_permanent_role = $role;
	}

	/**
	 * @return int
	 */
	public function getPermanentSwitchRole() : int
	{
		return $this->switch_permanent_role;
	}

	/**
	 * @param int $role
	 */
	public function setTemporarySwitchRole(int $role)
	{
		$this->switch_temporary_role = $role;
	}

	/**
	 * @return int
	 */
	public function getTemporarySwitchRole() : int
	{
		return $this->switch_temporary_role;
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
	 * @return void
	 */
	public function save()
	{
		if($this->is_persistent) {
			$this->update();
			return;
		}

		$query = 'insert into ' . self::TABLE_NAME . ' ' .
			'(oid, obj_id, switchp, switcht, status_created, modified) ' .
			'values ( '.
			$this->db->quote($this->getOid(),'text') . ', '.
			$this->db->quote($this->getObjId(),'integer') . ', '.
			$this->db->quote($this->getPermanentSwitchRole(),'integer'). ', '.
			$this->db->quote($this->getTemporarySwitchRole(),'integer'). ', '.
			$this->db->quote($this->getCreationStatus(), 'integer') . ', ' .
            $this->db->quote($this->getModified(), ilDBConstants::T_INTEGER) . ')';
		$this->db->manipulate($query);
		$this->is_persistent = true;
	}

	/**
	 * @return void
	 */
	protected function update()
	{
		$query = 'update ' . self::TABLE_NAME . ' '.
			'set ' .
			'obj_id = ' . $this->db->quote($this->getObjId(),'integer') . ', ' .
			'switchp = ' . $this->db->quote($this->getPermanentSwitchRole(),'integer') . ', ' .
			'switcht = ' . $this->db->quote($this->getTemporarySwitchRole(),'integer') . ', ' .
			'status_created = ' . $this->db->quote($this->getCreationStatus(),'integer') . ', ' .
            'modified = ' . $this->db->quote(time(), ilDBConstants::T_INTEGER) . ' ' .
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
			$this->setPermanentSwitchRole((int) $row->switchp);
			$this->setTemporarySwitchRole((int) $row->switcht);
			$this->setCreationStatus((int) $row->status_created);
			$this->setObjId((int) $row->obj_id);
			$this->setModified((int) $row->modified);
		}
	}

	/**
	 * @return int
	 */
	public function getObjId(): int
	{
		return $this->obj_id;
	}

	/**
	 * @param int $obj_id
	 */
	public function setObjId(int $obj_id): void
	{
		$this->obj_id = $obj_id;
	}
}