<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilVedaUserStatus
 */
class ilVedaCourse implements ilVedaCourseInterface
{
    private string $oid;
    private int $obj_id;
    private int $switch_permanent_role;
    private int $switch_temporary_role;
    private int $status_created;
    private int $modified;
    private int $type;

    private bool $document_success;

    public function __construct(
        string $oid,
        int $obj_id = 0,
        int $modified = 0,
        int $type = 0,
        int $switch_permanent_role = 0,
        int $switch_temporary_role = 0,
        int $status_created = ilVedaCourseStatus::NONE,
        bool $document_success = false
    ) {
        $this->oid = $oid;
        $this->obj_id = $obj_id;
        $this->modified = $modified;
        $this->type = $type;
        $this->switch_permanent_role = $switch_permanent_role;
        $this->switch_temporary_role = $switch_temporary_role;
        $this->status_created = $status_created;
        $this->document_success = $document_success;
    }

    public function getModified() : int
    {
        return $this->modified ?: time();
    }

    public function setModified(int $modified) : void
    {
        $this->modified = $modified;
    }

    public function setOid(string $oid) : void
    {
        $this->oid = $oid;
    }

    public function getOid() : string
    {
        return $this->oid;
    }

    public function getType() : int
    {
        return $this->type;
    }

    public function setType(int $type) : void
    {
        $this->type = $type;
    }

    public function setPermanentSwitchRole(int $role) : void
    {
        $this->switch_permanent_role = $role;
    }

    public function getPermanentSwitchRole() : int
    {
        return $this->switch_permanent_role;
    }

    public function setTemporarySwitchRole(int $role) : void
    {
        $this->switch_temporary_role = $role;
    }

    public function getTemporarySwitchRole() : int
    {
        return $this->switch_temporary_role;
    }

    public function setCreationStatus(int $status) : void
    {
        $this->status_created = $status;
    }

    public function getCreationStatus() : int
    {
        return $this->status_created;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id) : void
    {
        $this->obj_id = $obj_id;
    }

    public function setDocumentSuccess(bool $value) : void
    {
        $this->document_success = $value;
    }

    public function getDocumentSuccess() : bool
    {
        return $this->document_success;
    }

    public function toString() : string
    {
        return "Course with parameters: "
            . "\nOID: " . $this->oid
            . "\nObjID: " . $this->obj_id
            . "\nPRole: " . $this->switch_permanent_role
            . "\nTRole: " . $this->switch_temporary_role
            . "\nStatusCreated: " . $this->status_created
            . "\nModified: " . $this->modified
            . "\nType: " . $this->type;
    }
}
