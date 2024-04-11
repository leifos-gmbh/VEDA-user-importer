<?php

class ilVedaSegmentRepository implements ilVedaSegmentRepositoryInterface
{
    /**
     * @var string
     */
    protected const TABLE_NAME = 'cron_crnhk_vedaimp_seg';

    protected ilDBInterface $il_db;
    protected ilLogger $veda_logger;

    public function __construct(ilDBInterface $il_db, ilLogger $veda_logger)
    {
        $this->il_db = $il_db;
        $this->veda_logger = $veda_logger;
    }

    public function updateSegmentInfo(ilVedaSegmentInterface $veda_sgmt) : void
    {
        $this->veda_logger->debug('Updating segment with oid: ' . $veda_sgmt->getOID());
        $this->deleteSegmentInfo($veda_sgmt->getOID());
        $query = 'insert into ' . self::TABLE_NAME . ' ' .
            '(oid, type) ' .
            'values( ' .
            $this->il_db->quote($veda_sgmt->getOID(), ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote($veda_sgmt->getType(), ilDBConstants::T_TEXT) . ' ' .
            ')';
        $this->veda_logger->debug($query);
        $this->il_db->manipulate($query);
    }

    public function deleteSegmentInfo(string $oid) : void
    {
        $this->veda_logger->debug('Deleting segment with oid: ' . $oid);
        $query = 'delete from ' . self::TABLE_NAME . ' ' .
            'where oid = ' . $this->il_db->quote($oid, ilDBConstants::T_TEXT);
        $this->veda_logger->debug($query);
        $this->il_db->manipulate($query);
    }

    public function lookupSegmentInfo(string $oid) : ?ilVedaSegmentInterface
    {
        $this->veda_logger->debug('Looking up segment with oid: ' . $oid);
        $query = 'select type from ' . self::TABLE_NAME . ' ' .
            'where oid = ' . $this->il_db->quote($oid, ilDBConstants::T_TEXT);
        $this->veda_logger->debug($query);
        $res = $this->il_db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilVedaSegment($oid, $row->type);
        }
        return null;
    }
}
