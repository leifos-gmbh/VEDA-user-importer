<?php

interface ilVedaMailSegmentBuilderInterface
{
    public function withType(string $type): ilVedaMailSegmentBuilderInterface;

    public function withMessage(string $message): ilVedaMailSegmentBuilderInterface;

    public function store(): void;
}