<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
class ilVedaUser implements ilVedaUserInterface
{
    private string $oid;
    private string $login;
    private int $status_pwd;
    private int $status_created;
    private bool $import_failure;

    public function __construct(
        string $oid,
        string $login = '',
        int $status_pwd = ilVedaUserStatus::NONE,
        int $status_created = ilVedaUserStatus::NONE,
        bool $import_failure = false
    ) {
        $this->oid = $oid;
        $this->login = $login;
        $this->status_pwd = $status_pwd;
        $this->status_created = $status_created;
        $this->import_failure = $import_failure;
    }

    public function setOid(string $oid) : void
    {
        $this->oid = $oid;
    }

    public function getOid() : string
    {
        return $this->oid;
    }

    public function setLogin(string $login) : void
    {
        $this->login = $login;
    }

    public function getLogin() : string
    {
        return $this->login;
    }

    public function setPasswordStatus(int $status) : void
    {
        $this->status_pwd = $status;
    }

    public function getPasswordStatus() : int
    {
        return $this->status_pwd;
    }

    public function setCreationStatus(int $status) : void
    {
        $this->status_created = $status;
    }

    public function getCreationStatus() : int
    {
        return $this->status_created;
    }

    public function setImportFailure(bool $status) : void
    {
        $this->import_failure = $status;
    }

    public function isImportFailure() : bool
    {
        return $this->import_failure;
    }
}
