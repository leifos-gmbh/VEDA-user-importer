<?php

class ilVedaMDClaimingPluginDBManager implements ilVedaMDClaimingPluginDBManagerInterface
{
    protected ilLogger $logger;
    protected ilDBInterface $il_db;
    protected ilVedaMDClaimingPlugin $claiming_plugin;

    public function __construct(
        ilLogger $veda_logger,
        ilDBInterface $il_db,
        ilVedaMDClaimingPlugin $claiming_plugin
    ) {
        $this->logger = $veda_logger;
        $this->il_db = $il_db;
        $this->claiming_plugin = $claiming_plugin;
    }

    public function lookupSegmentId(int $ref_id): string
    {
        $obj_id = ilObject::_lookupObjId($ref_id);
        $fields = $this->claiming_plugin->getFields();
        $query = 'select value from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote($fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANGABSCHNITT], ilDBConstants::T_INTEGER) . ' ' .
            'and obj_id = ' . $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER) . ' ' .
            'and value != ' . $this->il_db->quote('', ilDBConstants::T_TEXT);
        $res = $this->il_db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->value;
        }
        return '';
    }

    public function lookupTrainingCourseId(int $ref_id): string
    {
        $obj_id = ilObject::_lookupObjId($ref_id);
        $fields = $this->claiming_plugin->getFields();
        $query = 'select value from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANG],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and obj_id = ' . $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER);
        $res = $this->il_db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->value) {
                return $row->value;
            }
        }
        return '';
    }

    public function deleteTrainingCourseSegmentId(int $ref_id): void
    {
        $obj_id = ilObject::_lookupObjId($ref_id);
        $fields = $this->claiming_plugin->getFields();
        $query = 'delete from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANGABSCHNITT],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and obj_id = ' . $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER);
        $this->il_db->manipulate($query);
    }

    public function deleteTrainingCourseSegmentTrainId(int $ref_id): void
    {
        $obj_id = ilObject::_lookupObjId($ref_id);
        $fields = $this->claiming_plugin->getFields();
        $query = 'delete from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUGABSCHNITT],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and obj_id = ' . $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER);
        $this->il_db->manipulate($query);
    }

    public function writeTrainingCourseSegmentTrainId(int $target_id, string $tc_oid) : void
    {
        $obj_id = ilObject::_lookupObjId($target_id);
        $fields = $this->claiming_plugin->getFields();

        $query = 'insert into adv_md_values_ltext (obj_id, field_id, value, disabled) ' .
            'values ( ' .
            $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER) . ', ' .
            $this->il_db->quote($fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUGABSCHNITT], ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote($tc_oid, ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote(1, ilDBConstants::T_INTEGER) .
            ')';
        $this->il_db->manipulate($query);
        $query = 'insert into adv_md_values_ltext (obj_id, field_id, value_index, value, disabled) ' .
            'values ( ' .
            $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER) . ', ' .
            $this->il_db->quote($fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUGABSCHNITT], ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote('de', ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote($tc_oid, ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote(1, ilDBConstants::T_INTEGER) .
            ')';
        $this->il_db->manipulate($query);
    }

    public function deleteTrainingCourseId(int $ref_id) : void
    {
        $obj_id = \ilObject::_lookupObjId($ref_id);
        $fields = $this->claiming_plugin->getFields();
        $query = 'delete from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANG],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and obj_id = ' . $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER);

        $this->il_db->manipulate($query);
    }

    public function deleteTrainingCourseTrainId(int $ref_id) : void
    {
        $obj_id = \ilObject::_lookupObjId($ref_id);
        $fields = $this->claiming_plugin->getFields();
        $query = 'delete from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUG],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and obj_id = ' . $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER);
        $this->il_db->manipulate($query);
    }

    public function writeTrainingCourseTrainId(int $target_id, string $tc_oid) : void
    {
        $obj_id = \ilObject::_lookupObjId($target_id);
        $fields = $this->claiming_plugin->getFields();

        $query = 'insert into adv_md_values_ltext (obj_id, field_id, value, disabled) ' .
            'values ( ' .
            $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER) . ', ' .
            $this->il_db->quote($fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUG], ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote($tc_oid, ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote(1, ilDBConstants::T_INTEGER) .
            ')';
        $this->logger->debug($query);
        $this->il_db->manipulate($query);
        $query = 'insert into adv_md_values_ltext (obj_id, field_id, value_index, value, disabled) ' .
            'values ( ' .
            $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER) . ', ' .
            $this->il_db->quote($fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUG], ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote('de', ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote($tc_oid, ilDBConstants::T_TEXT) . ', ' .
            $this->il_db->quote(1, ilDBConstants::T_INTEGER) .
            ')';

        $this->logger->debug($query);
        $this->il_db->manipulate($query);
    }

    public function findTrainingCourseId(int $ref_id) : string
    {
        $obj_id = ilObject::_lookupObjId($ref_id);
        $fields = $this->claiming_plugin->getFields();

        $query = 'select value from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANG],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and obj_id = ' . $this->il_db->quote($obj_id, ilDBConstants::T_INTEGER);
        $res = $this->il_db->query($query);
        $this->logger->dump($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->value) {
                return $row->value;
            }
        }
        return '';
    }

    /**
     * @return string[]
     */
    public function findTrainingCourseTrains() : array
    {
        $fields = $this->claiming_plugin->getFields();

        $query = 'select value from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUG],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and value != ' . $this->il_db->quote('', ilDBConstants::T_TEXT);

        $res = $this->il_db->query($query);

        $oids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!in_array($row->value, $oids)) {
                $oids[] = $row->value;
            }
        }
        return $oids;
    }

    /**
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public function findTrainingCourseTrain(?string $oid) : int
    {
        $fields = $this->claiming_plugin->getFields();

        $query = 'select obj_id from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUG],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and value = ' . $this->il_db->quote($oid, ilDBConstants::T_TEXT);
        $this->logger->alert($query);
        $res = $this->il_db->query($query);

        $ref_id = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // find ref_id
            $refs = \ilObject::_getAllReferences($row->obj_id);
            $ref = end($refs);
            $object = \ilObjectFactory::getInstanceByRefId($ref, false);
            if (!$object instanceof \ilObjCourse) {
                $this->logger->error('Found invalid "Ausbildungszug" with obj_id: ' . $row->obj_id);
                continue;
            }
            return $object->getRefId();
        }
        return 0;
    }

    /**
     * @return int[]
     * @throws ilDatabaseException
     */
    public function findTrainingCourseTemplates() : array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $fields = $this->claiming_plugin->getFields();

        $query = 'select obj_id from adv_md_values_ltext ' . ' ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANG],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and value != ' . $this->il_db->quote('', ilDBConstants::T_TEXT);
        $res = $this->il_db->query($query);

        $template_references = [];
        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (in_array($row->obj_id, $obj_ids)) {
                continue;
            }
            $obj_ids[] = $row->obj_id;

            // find ref_id
            $refs = ilObject::_getAllReferences($row->obj_id);
            $ref = end($refs);

            $object = ilObjectFactory::getInstanceByRefId($ref, false);
            if (!$object instanceof ilObjCourse) {
                $this->logger->error('Found invalid "Ausbildungsgang" with obj_id: ' . $row->obj_id);
                continue;
            }
            if ($tree->isDeleted($object->getRefId())) {
                $this->logger->notice('Ignoring deleted course with obj_id: ' . $row->obj_id);
                continue;
            }
            $template_references[] = $object->getRefId();
        }

        return $template_references;
    }

    public function findTrainSegmentId(int $ref_id) : string
    {
        $obj_id = ilObject::_lookupObjId($ref_id);
        $fields = $this->claiming_plugin->getFields();

        $query = 'select value from adv_md_values_ltext ' .
            'where field_id = ' . $this->il_db->quote(
                $fields[ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUGABSCHNITT],
                ilDBConstants::T_INTEGER
            ) . ' ' .
            'and obj_id = ' . $this->il_db->quote(
                $obj_id,
                ilDBConstants::T_INTEGER
            );
        $res = $this->il_db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->value) {
                return $row->value;
            }
        }
        return '';
    }
}