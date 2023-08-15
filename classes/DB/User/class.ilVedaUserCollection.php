<?php

class ilVedaUserCollection implements ilVedaUserCollectionInterface
{
    /**
     * @var ilVedaUserInterface[]
     */
    protected array $veda_usrs;
    protected int $index;

    /**
     * @param ilVedaUserInterface[] $veda_usrs
     */
    public function __construct(array $veda_usrs)
    {
        $this->veda_usrs = $veda_usrs;
        $this->index = 0;
    }

    public function getUsersWithPendingCreationStatus(): ilVedaUserCollectionInterface
    {
        $pending_participants = [];
        foreach ($this->veda_usrs as $veda_usr) {
            if(
                $veda_usr->getCreationStatus() === ilVedaUserStatus::PENDING ||
                (
                    $veda_usr->getCreationStatus() === ilVedaUserStatus::NONE &&
                    !$veda_usr->isImportFailure()
                )
            ) {
                $pending_participants[] = $veda_usr;
            }
        }
        return new ilVedaUserCollection($pending_participants);
    }

    public function count(): int
    {
        return count($this->veda_usrs);
    }

    public function current(): ilVedaUserInterface
    {
        return $this->veda_usrs[$this->index];
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
        return 0 <= $this->index && $this->index < count($this->veda_usrs);
    }
}