<?php

interface ilVedaSegmentBuilderInterface
{
    public function withType(string $type): ilVedaSegmentBuilderInterface;

    public function withOID(string $oid, bool $load_from_db = true): ilVedaSegmentBuilderInterface;

    public function get(): ilVedaSegmentInterface;

    public function store(): void;
}