<?php

interface ilVedaUserBuilderInterface
{
    public function withOID(string $oid, bool $load_from_db = true) : ilVedaUserBuilderInterface;

    public function withLogin(string $login) : ilVedaUserBuilderInterface;

    public function withPasswordStatus(int $status) : ilVedaUserBuilderInterface;

    public function withCreationStatus(int $status) : ilVedaUserBuilderInterface;

    public function withImportFailure(bool $value) : ilVedaUserBuilderInterface;

    public function get() : ilVedaUserInterface;

    public function store() : void;
}
