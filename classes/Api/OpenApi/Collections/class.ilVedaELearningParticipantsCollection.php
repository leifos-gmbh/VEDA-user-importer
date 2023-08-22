<?php

use OpenAPI\Client\Model\TeilnehmerELearningPlattform;

class ilVedaELearningParticipantsCollection implements ilVedaELearningParticipantsCollectionInterface
{
    /**
     * @var TeilnehmerELearningPlattform[]
     */
    protected array $elearning_participants;
    protected int $index;

    /**
     * @param TeilnehmerELearningPlattform[] $elearning_participants
     */
    public function __construct(array $elearning_participants)
    {
        $this->elearning_participants = $elearning_participants;
        $this->index = 0;
    }

    public function current(): TeilnehmerELearningPlattform
    {
        return $this->elearning_participants[$this->index];
    }

    public function key(): int
    {
        return $this->index;
    }

    public function next(): void
    {
        $this->index++;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return 0 <= $this->index && $this->index < count($this->elearning_participants);
    }

    public function count(): int
    {
        return count($this->elearning_participants);
    }
}