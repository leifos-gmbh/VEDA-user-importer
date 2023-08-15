<?php

interface ilVedaUserInterface
{
    public function setOid(string $oid) : void;

    public function getOid() : string;

    public function setLogin(string $login) : void;

    public function getLogin() : string;

    public function setPasswordStatus(int $status) : void;

    public function getPasswordStatus() : int;

    public function setCreationStatus(int $status) : void;

    public function getCreationStatus() : int;

    public function setImportFailure(bool $status) : void;

    public function isImportFailure() : bool;
}
