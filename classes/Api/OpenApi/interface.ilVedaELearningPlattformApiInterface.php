<?php

use OpenAPI\Client\Model\Ausbildungszug;
use OpenAPI\Client\Model\Dozentenkurszuordnung;
use OpenAPI\Client\Model\Elearningkurs;
use OpenAPI\Client\Model\Lernbegleiterkurszuordnung;
use OpenAPI\Client\Model\TeilnehmerELearningPlattform;
use OpenAPI\Client\Model\Teilnehmerkurszuordnung;

interface ilVedaELearningPlattformApiInterface
{
    /**
     * @return Teilnehmerkurszuordnung[]
     */
    public function requestCourseMembers(string $crs_oid) : array;

    /**
     * @return Lernbegleiterkurszuordnung[]
     */
    public function requestCourseSupervisors(string $crs_oid) : array;

    /**
     * @return Dozentenkurszuordnung[]
     */
    public function requestCourseTutors(string $crs_oid) : array;

    /**
     * @return Elearningkurs[]
     */
    public function requestCourses() : array;

    /**
     * @return Ausbildungszug[]
     */
    public function requestTrainingCourseTrains(string $training_course_id) : array;

    /**
     * @return TeilnehmerELearningPlattform[]
     */
    public function requestParticipants() : array;

    public function sendCourseCopyStarted(string $crs_oid) : void;

    public function sendCourseCreationFailed(string $crs_oid, string $message) : void;

    public function sendCourseCreated(string $crs_oid) : void;

    public function sendParticipantAssignedToCourse(string $crs_oid, string $usr_oid) : void;

    public function sendAccountCreated(string $participant_id) : void;

    public function sendAccountCreationFailed(string $usr_oid, string $message) : void;

    public function sendCoursePassed(string $crs_oid, string $usr_oid) : void;

    public function sendCourseFailed(string $crs_oid, string $usr_oid) : void;

    public function sendFirstLoginSuccess(string $usr_oid) : void;
}
