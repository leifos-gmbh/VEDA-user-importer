<?php

class ilVedaUserRepository implements ilVedaUserRepositoryInterface
{
    /**
     * @var string
     */
    private const TABLE_NAME = 'cron_crnhk_vedaimp_us';

    protected ilDBInterface $il_db;
    protected ilLogger $veda_logger;

    public function __construct(ilDBInterface $il_db, ilLogger $il_logger)
    {
        $this->il_db = $il_db;
        $this->veda_logger = $il_logger;
    }

    protected function refIDtoOID(int $ref_id) : ?string
    {
        $import_id = ilObjUser::_lookupImportId($ref_id);
        if (!$import_id) {
            $this->veda_logger->debug('No veda user for event found.');
            return null;
        }
        return $import_id;
    }

    public function updateUser(ilVedaUserInterface $user_status) : void
    {
        $this->veda_logger->debug('Updating user with oid: ' . $user_status->getOid());
        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (oid, login, status_pwd, status_created, import_failure)'
            . ' VALUES ('
            . $this->il_db->quote($user_status->getOid(), 'text') . ', '
            . $this->il_db->quote($user_status->getLogin(), 'text') . ', '
            . $this->il_db->quote($user_status->getPasswordStatus(), 'integer') . ', '
            . $this->il_db->quote($user_status->getCreationStatus(), 'integer') . ', '
            . $this->il_db->quote($user_status->isImportFailure(), 'integer')
            . ') ON DUPLICATE KEY UPDATE '
            . 'oid=VALUES(oid), '
            . 'login=VALUES(login), '
            . 'status_pwd=VALUES(status_pwd), '
            . 'status_created=VALUES(status_created), '
            . 'import_failure=VALUES(import_failure)';
        $this->veda_logger->debug($query);
        $this->il_db->manipulate($query);
    }

    public function createEmptyUser(string $oid): ilVedaUserInterface
    {
        return new ilVedaUser($oid);
    }

    public function deleteUserByOID(string $oid) : void
    {
        $this->veda_logger->debug('Deleting user by oid: ' . $oid);
        $query = 'delete from ' . self::TABLE_NAME . ' ' .
            'where oid = ' . $this->il_db->quote($oid, 'text');
        $this->veda_logger->debug($query);
        $this->il_db->manipulate($query);
    }

    public function deleteUserByID(int $usr_id) : void
    {
        $this->veda_logger->debug('Deleting user by id: ' . $usr_id);
        $import_id = $this->refIDtoOID($usr_id);
        if (!is_null($import_id)) {
            $this->deleteUserByOID($import_id);
        }
    }

    public function lookupUserByOID(string $oid) : ?ilVedaUserInterface
    {
        $this->veda_logger->debug('Lookup user by oid: ' . $oid);
        $query = 'select * from ' . self::TABLE_NAME . ' ' .
            'where oid = ' . $this->il_db->quote($oid, 'text');
        $this->veda_logger->debug($query);
        $res = $this->il_db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilVedaUser(
                $row->oid,
                $row->login,
                (int) $row->status_pwd,
                (int) $row->status_created,
                (int) $row->import_failure,
            );
        }
        return null;
    }

    public function lookupUserByID(int $ref_id) : ?ilVedaUserInterface
    {
        $this->veda_logger->debug('Lookup user by ref_id: ' . $ref_id);
        $oid = $this->refIDtoOID($ref_id);
        if (is_null($oid)) {
            return null;
        }
        return $this->lookupUserByOID($oid);
    }

    public function lookupAllUsers() : ilVedaUserCollectionInterface
    {
        $this->veda_logger->debug('Looking up all users.');
        $query = 'select * from ' . self::TABLE_NAME;
        $this->veda_logger->debug($query);
        $res = $this->il_db->query($query);
        $all_users = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $all_users[] = new ilVedaUser(
                $row->oid,
                $row->login,
                (int) $row->status_pwd,
                (int) $row->status_created,
                (int) $row->import_failure,
            );
        }
        return new ilVedaUserCollection($all_users);
    }
}
