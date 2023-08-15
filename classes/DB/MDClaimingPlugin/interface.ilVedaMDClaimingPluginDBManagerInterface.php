<?php

interface ilVedaMDClaimingPluginDBManagerInterface
{
    public function lookupSegmentId(int $ref_id): string;

    public function lookupTrainingCourseId(int $ref_id): string;

    public function deleteTrainingCourseSegmentId(int $ref_id): void;

    public function deleteTrainingCourseSegmentTrainId(int $ref_id): void;

    public function deleteTrainingCourseId(int $ref_id) : void;

    public function writeTrainingCourseSegmentTrainId(int $target_id, string $tc_oid) : void;

    public function deleteTrainingCourseTrainId(int $ref_id) : void;

    public function writeTrainingCourseTrainId(int $target_id, string $tc_oid) : void;

    public function findTrainingCourseId(int $ref_id) : string;

    public function findTrainingCourseTrains() : array;

    public function findTrainingCourseTrain(?string $oid) : int;

    public function findTrainingCourseTemplates() : array;

    public function findTrainSegmentId(int $ref_id) : string;
}