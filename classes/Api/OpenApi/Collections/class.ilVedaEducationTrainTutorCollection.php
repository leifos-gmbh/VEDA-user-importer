<?php

use OpenAPI\Client\Model\AusbildungszugDozent;

class ilVedaEducationTrainTutorCollection implements ilVedaEducationTrainTutorCollectionInterface
{
    /**
     * @var AusbildungszugDozent[]
     */
    protected array $education_train_tutors;
    protected int $index;

    /**
     * @param AusbildungszugDozent $education_train_tutors
     */
    public function __construct(array $education_train_tutors)
    {
        $this->education_train_tutors = $education_train_tutors;
        $this->index = 0;
    }

    public function logContent(ilLogger $logger) : void
    {
        $logger->dump($this->education_train_tutors, ilLogLevel::DEBUG);
    }

    public function current() : AusbildungszugDozent
    {
        return $this->education_train_tutors[$this->index];
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
        return 0 <= $this->index && $this->index < count($this->education_train_tutors);
    }

    public function count() : int
    {
        return count($this->education_train_tutors);
    }
}
