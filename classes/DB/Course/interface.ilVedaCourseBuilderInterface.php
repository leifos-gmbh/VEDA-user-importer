<?php

interface ilVedaCourseBuilderInterface
{
    /**
     * Attempts to load an existing course from the ilias database
     */
    public function withOID(string $oid, bool $load_from_db = true): ilVedaCourseBuilderInterface;

    public function withObjID(int $obj_id): ilVedaCourseBuilderInterface;

    public function withSwitchPermanentRole(int $role_id): ilVedaCourseBuilderInterface;

    public function withSwithTemporaryRole(int $role_id): ilVedaCourseBuilderInterface;

    public function withStatusCreated(int $status): ilVedaCourseBuilderInterface;

    public function withModified(int $modified): ilVedaCourseBuilderInterface;

    public function withType(int $type): ilVedaCourseBuilderInterface;

    public function withDocumentSuccess(bool $value): ilVedaCourseBuilderInterface;

    public function store(): void;

    public function get(): ilVedaCourseInterface;
}