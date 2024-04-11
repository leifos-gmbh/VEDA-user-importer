<?php

use OpenAPI\Client\Model\AufsichtspersonKurszugriff;

class ilVedaEducationTrainSupervisorCollection implements ilVedaEducationTrainSupervisorCollectionInterface
{
    /**
     * @var AufsichtspersonKurszugriff[]
     */
    protected array $education_train_supervisor;
    protected int $index;

    /**
     * @param array<AufsichtspersonKurszugriff> $education_train_supervisor
     */
    public function __construct(array $education_train_supervisor)
    {
        $this->education_train_supervisor = $education_train_supervisor;
        $this->index = 0;
    }

    public function logContent(ilLogger $logger) : void
    {
        $logger->dump($this->education_train_supervisor, ilLogLevel::DEBUG);
    }

    public function current() : AufsichtspersonKurszugriff
    {
        return $this->education_train_supervisor[$this->index];
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
        return 0 <= $this->index && $this->index < count($this->education_train_supervisor);
    }

    public function count() : int
    {
        return count($this->education_train_supervisor);
    }
}
