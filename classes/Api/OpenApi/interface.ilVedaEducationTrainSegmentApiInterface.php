<?php

interface ilVedaEducationTrainSegmentApiInterface
{
    public function sendExerciseSubmissionConfirmed(
        string $segment_oid,
        string $participant_oid,
        DateTime $confirmed = null
    ) : void;

    public function sendExerciseSubmissionDate(
        string $segment_oid,
        string $participant_oid,
        ?DateTime $subdate = null
    ) : void;

    public function sendExerciseSuccess(string $segment_oid, string $participant_oid, \DateTime $dt) : void;
}
