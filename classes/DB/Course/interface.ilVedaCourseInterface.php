<?php

interface ilVedaCourseInterface
{
    public function getModified() : int;

    public function setModified(int $modified) : void;

    public function setOid(string $oid);

    public function getOid() : string;

    public function getType() : int;

    public function setType(int $type) : void;

    public function setPermanentSwitchRole(int $role): void;

    public function getPermanentSwitchRole() : int;

    public function setTemporarySwitchRole(int $role): void;

    public function getTemporarySwitchRole() : int;

    public function setCreationStatus(int $status): void;

    public function getCreationStatus() : int;

    public function getObjId() : int;

    public function setObjId(int $obj_id) : void;

    public function setDocumentSuccess(bool $value): void;

    public function getDocumentSuccess(): bool;

    public function toString(): string;
}