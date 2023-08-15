<?php

interface ilVedaCourseRepositoryInterface
{
    public function updateCourse(ilVedaCourseInterface $course_status): void;

    public function deleteCourseByOID(string $oid): void;

    public function lookupCourseByOID(string $oid): ?ilVedaCourseInterface;

    public function lookupCourseByID(int $ref_id): ?ilVedaCourseInterface;

    public function lookupAllCourses(): ilVedaCourseCollectionInterface;
}