<?php

interface ilVedaEducationTrainSegmentApiInterface
{
    public function sendExerciseSubmissionConfirmed(
        string $segment_oid,
        string $participant_oid,
        DateTime $confirmed = null
    ) : bool;

    public function sendExerciseSubmissionDate(
        string $segment_oid,
        string $participant_oid,
        ?DateTime $subdate = null
    ) : bool;

    public function sendExerciseSuccess(
        string $segment_oid,
        string $participant_oid,
        \DateTime $dt
    ) : bool;
}
