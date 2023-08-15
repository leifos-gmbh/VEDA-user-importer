<?php

interface ilVedaCourseCollectionInterface extends Iterator, Countable
{
    public function getCoursesWithStatusAndType(int $status, int $type): ilVedaCourseCollectionInterface;

    public function getAsynchronusCourses(): ilVedaCourseCollectionInterface;

    public function count(): int;

    public function current(): ilVedaCourseInterface;

    public function key(): int;

    public function next(): void;

    public function rewind(): void;

    public function valid(): bool;
}