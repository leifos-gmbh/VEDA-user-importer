<?php

class ilVedaSegmentRepository implements ilVedaSegmentRepositoryInterface
{
    /**
     * @var string
     */
    protected const TABLE_NAME = 'cron_crnhk_vedaimp_seg';

    protected ilDBInterface $il_db;

    public function __construct(ilDBInterface $il_db)
    {
        $this->il_db = $il_db;
    }

    public function updateSegmentInfo(ilVedaSegmentInterface $segment_status) : void
    {
        $this->deleteSegmentInfo($segment_status->getOID());
        $query = 'insert into ' . self::TABLE_NAME . ' ' .
            '(oid, type) ' .
            'values( ' .
            $this->il_db->quote($segment_status->getOID(), \ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote($segment_status->getOID(), \ilDBConstants::T_TEXT) . ' ' .
            ')';
        $this->il_db->manipulate($query);
    }

    public function deleteSegmentInfo(string $oid) : void
    {
        $query = 'delete from ' . self::TABLE_NAME . ' ' .
            'where oid = ' . $this->il_db->quote($oid, \ilDBConstants::T_TEXT);
        $this->il_db->manipulate($query);
    }

    public function lookupSegmentInfo(string $oid) : ?ilVedaSegmentInterface
    {
        $query = 'select type from ' . self::TABLE_NAME . ' ' .
            'where oid = ' . $this->il_db->quote($oid, \ilDBConstants::T_TEXT);
        $res = $this->il_db->query($query);
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilVedaSegment(
                $oid,
                $row->type
            );
        }
        return null;
    }

    public function createEmptySegment(string $oid) : ilVedaSegment
    {
        return new ilVedaSegment($oid);
    }
}
