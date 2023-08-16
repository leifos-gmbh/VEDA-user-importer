<?php

interface ilVedaApiInterface
{
    public function handleParticipantAssignedToCourse(int $obj_id, int $usr_id, int $role_id) : void;

    public function handleAfterCloningDependenciesSIFAEvent(int $source_id, int $target_id, int $copy_id) : void;

    public function handleAfterCloningDependenciesStandardEvent(int $source_id, int $target_id, int $copy_id) : void;

    public function handleAfterCloningSIFAEvent(int $a_source_id, int $a_target_id, int $a_copy_id) : void;

    public function handleAfterCloningStandardEvent(int $a_source_id, int $a_target_id, int $a_copy_id) : void;

    public function handleTrackingEvent(int $obj_id, int $usr_id, int $status) : void;

    public function handlePasswordChanged(int $usr_id) : void;

    public function handleCloningFailed() : void;

    public function deleteDeprecatedILIASUsers() : void;

    public function importILIASUsers() : void;

    public function importStandardCourses() : void;

    public function importSIFACourses() : void;

    public function importSIFAMembers() : void;

    public function importStandardMembers() : void;

    public function isTrainingCourseValid(string $course_oid) : bool;

    /**
     * @return string[]
     */
    public function validateRemoteExercises(array $exercises, string $course_oid) : array;

    public function validateLocalExercises(array $exercises, string $course_oid) : array;

    public function validateLocalSessions(array $sessions, string $course_oid) : array;

    /**
     * @return string[]
     */
    public function validateRemoteSessions(array $sessions, string $course_oid) : array;

    public function testConnection() : bool;
}
