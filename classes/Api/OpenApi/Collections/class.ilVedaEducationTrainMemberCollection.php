<?php

use OpenAPI\Client\Model\AusbildungszugTeilnehmer;

class ilVedaEducationTrainMemberCollection implements ilVedaEducationTrainMemberCollectionInterface
{
    /**
     * @var AusbildungszugTeilnehmer[]
     */
    protected array $education_train_participants;
    protected int $index;

    /**
     * @param array<AusbildungszugTeilnehmer> $education_train_participants
     */
    public function __construct(array $education_train_participants)
    {
        $this->education_train_participants = $education_train_participants;
        $this->index = 0;
    }

    public function logContent(ilLogger $logger) : void
    {
        $logger->dump($this->education_train_participants, ilLogLevel::DEBUG);
    }

    public function current() : AusbildungszugTeilnehmer
    {
        return $this->education_train_participants[$this->index];
    }

    public function key() : int
    {
        return $this->index;
    }

    public function next() : void
    {
        $this->index++;
    }

    public function rewind() : void
    {
        $this->index = 0;
    }

    public function valid() : bool
    {
        return 0 <= $this->index && $this->index < count($this->education_train_participants);
    }

    public function count() : int
    {
        return count($this->education_train_participants);
    }
}
