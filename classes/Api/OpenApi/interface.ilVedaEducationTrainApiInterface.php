<?php

use OpenAPI\Client\Model\AufsichtspersonKurszugriff;
use OpenAPI\Client\Model\AusbildungszugDozent;
use OpenAPI\Client\Model\AusbildungszugLernbegleiter;
use OpenAPI\Client\Model\AusbildungszugTeilnehmer;

interface ilVedaEducationTrainApiInterface
{
    public function requestTutors(?string $oid) : ilVedaEducationTrainTutorCollectionInterface;

    public function requestCompanions(?string $oid) : ilVedaEducationTrainCompanionCollectionInterface;

    public function requestSupervisors(?string $oid) : ilVedaEducationTrainSupervisorCollectionInterface;

    public function requestMembers(?string $oid) : ilVedaEducationTrainMemberCollectionInterface;

    public function sendCourseCreationFailed(string $oid) : void;

    public function sendCourseCreated(string $oid) : void;

    public function sendCopyStarted(string $oid) : void;
}
