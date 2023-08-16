<?php

use OpenAPI\Client\Model\AufsichtspersonKurszugriff;
use OpenAPI\Client\Model\AusbildungszugDozent;
use OpenAPI\Client\Model\AusbildungszugLernbegleiter;
use OpenAPI\Client\Model\AusbildungszugTeilnehmer;

interface ilVedaEducationTrainApiInterface
{
    /**
     * @return AusbildungszugDozent[]
     */
    public function requestTutors(?string $oid) : array;

    /**
     * @return AusbildungszugLernbegleiter[]
     */
    public function requestCompanions(?string $oid) : array;

    /**
     * @return AufsichtspersonKurszugriff[]
     */
    public function requestSupervisors(?string $oid) : array;

    /**
     * @return AusbildungszugTeilnehmer[]
     */
    public function requestMembers(?string $oid) : array;

    public function sendCourseCreationFailed(string $oid) : void;

    public function sendCourseCreated(string $oid) : void;

    public function sendCopyStarted(string $oid) : void;
}
