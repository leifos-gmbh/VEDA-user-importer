<?php

class ilVedaCourseRepository implements ilVedaCourseRepositoryInterface
{
    /**
     * @var string
     */
    protected const TABLE_NAME = 'cron_crnhk_vedaimp_crs';

    protected ilDBInterface $il_db;
    protected ilLogger $veda_logger;

    public function __construct(ilDBInterface $il_db, ilLogger $il_logger)
    {
        $this->il_db = $il_db;
        $this->veda_logger = $il_logger;
    }

    protected function refIDtoOID(int $ref_id): ?string
    {
        $import_id = ilObjCourse::_lookupImportId($ref_id);
        if (!$import_id) {
            $this->veda_logger->debug('No veda user for event found.');
            return null;
        }
        return $import_id;
    }

    public function updateCourse(ilVedaCourseInterface $course_status): void {
        $this->veda_logger->debug('Updating course with oid: ' . $course_status->getOid());
        $query = 'INSERT INTO ' . self::TABLE_NAME
            . ' (oid, obj_id, switchp, switcht, status_created, modified, type, document_success) '
            . ' VALUES ('
            . $this->il_db->quote($course_status->getOid(), ilDBConstants::T_TEXT) . ', '
            . $this->il_db->quote($course_status->getObjId(), ilDBConstants::T_INTEGER) . ', '
            . $this->il_db->quote($course_status->getPermanentSwitchRole(), ilDBConstants::T_INTEGER) . ', '
            . $this->il_db->quote($course_status->getTemporarySwitchRole(), ilDBConstants::T_INTEGER) . ', '
            . $this->il_db->quote($course_status->getCreationStatus(), ilDBConstants::T_INTEGER) . ', '
            . $this->il_db->quote($course_status->getModified(), ilDBConstants::T_INTEGER) . ', '
            . $this->il_db->quote($course_status->getType(), ilDBConstants::T_INTEGER) . ', '
            . $this->il_db->quote((int) $course_status->getDocumentSuccess(), ilDBConstants::T_INTEGER)
            . ') ON DUPLICATE KEY UPDATE '
            . 'oid=VALUES(oid), '
            . 'obj_id=VALUES(obj_id), '
            . 'switchp=VALUES(switchp), '
            . 'switcht=VALUES(switcht), '
            . 'status_created=VALUES(status_created), '
            . 'modified=VALUES(modified), '
            . 'type=VALUES(type), '
            . 'document_success=VALUES(document_success)';
        $this->veda_logger->debug($query);
        $this->il_db->manipulate($query);
    }

    public function deleteCourseByOID(string $oid): void
    {
        $this->veda_logger->debug('Deleting course with oid: ' . $oid);
        $query = 'delete from ' . self::TABLE_NAME . ' '
            . 'where oid = ' . $this->il_db->quote($oid, ilDBConstants::T_TEXT);
        $this->veda_logger->debug($query);
        $this->il_db->manipulate($query);
    }

    public function lookupCourseByOID(string $oid): ?ilVedaCourseInterface
    {
        $this->veda_logger->debug('Looking up course by oid: ' . $oid);
        $query = 'select * from ' . self::TABLE_NAME . ' ' .
            'where oid = ' . $this->il_db->quote($oid, ilDBConstants::T_TEXT);
        $this->veda_logger->debug($query);
        $res = $this->il_db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilVedaCourse(
                $row->oid,
                (int) $row->obj_id,
                (int) $row->modified,
                (int) $row->type,
                (int) $row->switchp,
                (int) $row->switcht,
                (int) $row->status_created,
                (bool) $row->document_success
            );
        }
        return null;
    }

    public function lookupCourseByID(int $ref_id): ?ilVedaCourseInterface
    {
        $this->veda_logger->debug('Lookup up course by ref_id: ' . $ref_id);
        $oid = $this->refIDtoOID($ref_id);
        if (is_null($oid)) {
            return null;
        }
        return $this->lookupCourseByOID($oid);
    }

    public function lookupAllCourses(): ilVedaCourseCollectionInterface
    {
        $this->veda_logger->debug('Looking up all courses.');
        $query = 'select * from ' . self::TABLE_NAME;
        $this->veda_logger->debug($query);
        $res = $this->il_db->query($query);
        $courses = [];
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            $courses[] = new ilVedaCourse(
                $row->oid,
                (int) $row->obj_id,
                (int) $row->modified,
                (int) $row->type,
                (int) $row->switchp,
                (int) $row->switcht,
                (int) $row->status_created,
                (bool) $row->document_success
            );
        }
        $this->veda_logger->debug('Found ' . count($courses));
        return new ilVedaCourseCollection($courses);
    }
}