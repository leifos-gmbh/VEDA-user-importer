<?php

interface ilVedaEducationTrainApiInterface
{
    public function requestTutors(?string $oid) : ?ilVedaEducationTrainTutorCollectionInterface;

    public function requestCompanions(?string $oid) : ?ilVedaEducationTrainCompanionCollectionInterface;

    public function requestSupervisors(?string $oid) : ?ilVedaEducationTrainSupervisorCollectionInterface;

    public function requestMembers(?string $oid) : ?ilVedaEducationTrainMemberCollectionInterface;

    public function sendCourseCreationFailed(string $oid) : bool;

    public function sendCourseCreated(string $oid) : bool;

    public function sendCopyStarted(string $oid) : bool;
}
