<?php

use OpenAPI\Client\Model\Ausbildungszug;

class ilVedaEducationTrainCourseCollection implements ilVedaEducationTrainCourseCollectionInterface
{
    /**
     * @var Ausbildungszug[]
     */
    protected array $education_trains;
    protected int $index;

    /**
     * @param Ausbildungszug[] $education_trains
     */
    public function __construct(array $education_trains)
    {
        $this->education_trains = $education_trains;
        $this->index = 0;
    }

    public function getByOID(string $oid) : ?Ausbildungszug
    {
        foreach ($this->education_trains as $train) {
            if (ilVedaUtils::compareOidsEqual($train->getOid(), $oid)) {
                return $train;
            }
        }
        return null;
    }

    public function current() : Ausbildungszug
    {
        return $this->education_trains[$this->index];
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
        return 0 <= $this->index && $this->index < count($this->education_trains);
    }

    public function count() : int
    {
        return count($this->education_trains);
    }
}
