<?php

use OpenAPI\Client\Model\Ausbildungszug;
use OpenAPI\Client\Model\Elearningkurs;
use OpenAPI\Client\Model\TeilnehmerELearningPlattform;

interface ilVedaELearningPlattformApiInterface
{
    public function requestCourseMembers(string $crs_oid) : ilVedaCourseMemberCollectionInterface;

    public function requestCourseSupervisors(string $crs_oid) : ilVedaCourseSupervisorCollectionInterface;

    public function requestCourseTutors(string $crs_oid) : ilVedaCourseTutorsCollectionInterface;

    public function requestCourses() : ilVedaELearningCourseCollectionInterface;

    public function requestTrainingCourseTrains(string $training_course_id) : ilVedaEducationTrainCourseCollectionInterface;

    public function requestParticipants() : ilVedaELearningParticipantsCollectionInterface;

    public function sendCourseCopyStarted(string $crs_oid) : void;

    public function sendCourseCreationFailed(string $crs_oid, string $message) : void;

    public function sendCourseCreated(string $crs_oid) : void;

    public function sendParticipantStartedCourseWork(string $crs_oid, string $usr_oid) : void;

    public function sendAccountCreated(string $participant_id) : void;

    public function sendAccountCreationFailed(string $usr_oid, string $message) : void;

    public function sendCoursePassed(string $crs_oid, string $usr_oid) : void;

    public function sendCourseFailed(string $crs_oid, string $usr_oid) : void;

    public function sendFirstLoginSuccess(string $usr_oid) : void;
}
