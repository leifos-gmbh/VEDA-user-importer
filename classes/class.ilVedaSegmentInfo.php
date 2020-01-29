<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores segment (train) info
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaSegmentInfo
{
    /**
     * @var string
     */
	public const TYPE_PRAKTIKUM = 'Praktikum';

    /**
     * @var string
     */
	public const TYPE_SELF_LEARNING = 'Selbstlernen';

    /**
     * @var string
     */
	private const TABLE_NAME = 'cron_crnhk_vedaimp_seg';

	/**
	 * @var string
	 */
	private $oid = '';

	private $type = '';


	/**
	 * @var \ilDBInterface
	 */
	private $db;

	/**
	 * ilVedaSegmentInfo constructor.
	 * @param string $oid
	 * @param string $type
	 */
	public function __construct(string $oid, string $type)
	{
		global $DIC;

		$this->db = $DIC->database();

		$this->oid = $oid;
		$this->type = $type;
	}

	/**
	 *
	 */
	public function update()
	{
		$this->delete();

		$query = 'insert into ' . self::TABLE_NAME . ' ' .
			'(oid, type) ' .
			'values( ' .
			$this->db->quote($this->oid, \ilDBConstants::T_TEXT) . ', '.
			$this->db->quote($this->type, \ilDBConstants::T_TEXT). ' ' .
			')';
		$this->db->manipulate($query);
	}

	/**
	 *
	 */
	public function delete()
	{
		$query = 'delete from ' . self::TABLE_NAME . ' '.
			'where oid = ' . $this->db->quote($this->oid, \ilDBConstants::T_TEXT);
		$this->db->manipulate($query);
	}

    /**
     * @param string|null $oid
     * @return bool
     * @throws ilDatabaseException
     */
	public static function isSelfLearning(?string $oid) : bool
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'select type from ' . self::TABLE_NAME . ' '.
            'where oid = ' . $db->quote($oid, \ilDBConstants::T_TEXT);
        $res = $db->query($query);
        while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            if($row->type == self::TYPE_PRAKTIKUM) {
                return true;
            }
        }
        return false;


    }

	/**
	 * @param string|null $oid
	 * @return bool
	 * @throws \ilDatabaseException
	 */
	public static function isPracticalTraining(?string $oid)
	{
		global $DIC;

		$db = $DIC->database();

		$query = 'select type from ' . self::TABLE_NAME . ' '.
			'where oid = ' . $db->quote($oid, \ilDBConstants::T_TEXT);
		$res = $db->query($query);
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
			if($row->type == self::TYPE_SELF_LEARNING) {
				return true;
			}
		}
		return false;
	}



}
