<?php

interface ilVedaELearningPlattformApiInterface
{
    public function requestCourseMembers(string $crs_oid) : ?ilVedaCourseMemberCollectionInterface;

    public function requestCourseCompanions(string $crs_oid) : ?ilVedaCourseCompanionCollectionInterface;

    public function requestCourseTutors(string $crs_oid) : ?ilVedaCourseTutorsCollectionInterface;

    public function requestCourses() : ?ilVedaELearningCourseCollectionInterface;

    public function requestTrainingCourseTrains(string $training_course_id) : ?ilVedaEducationTrainCourseCollectionInterface;

    public function requestParticipants() : ?ilVedaELearningParticipantsCollectionInterface;

    public function sendCourseCopyStarted(string $crs_oid) : bool;

    public function sendCourseCreationFailed(string $crs_oid, string $message) : bool;

    public function sendCourseCreated(string $crs_oid) : bool;

    public function sendParticipantStartedCourseWork(string $crs_oid, string $usr_oid) : bool;

    public function sendAccountCreated(string $participant_id) : bool;

    public function sendAccountCreationFailed(string $usr_oid, string $message) : bool;

    public function sendCoursePassed(string $crs_oid, string $usr_oid) : bool;

    public function sendCourseFailed(string $crs_oid, string $usr_oid) : bool;

    public function sendFirstLoginSuccess(string $usr_oid) : bool;
}
