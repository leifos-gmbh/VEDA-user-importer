<?php

class ilVedaCourseCollection implements ilVedaCourseCollectionInterface
{
    /**
     * @var ilVedaCourseInterface[]
     */
    protected array $veda_crss;
    protected int $index;

    /**
     * @param ilVedaCourseInterface[] $veda_crss
     */
    public function __construct(array $veda_crss)
    {
        $this->veda_crss = $veda_crss;
        $this->index = 0;
    }

    public function getCoursesWithStatusAndType(int $status, int $type) : ilVedaCourseCollectionInterface
    {
        $found_crss = [];
        foreach ($this->veda_crss as $veda_crs) {
            if (
                $veda_crs->getType() === $type &&
                $veda_crs->getCreationStatus() === $status
            ) {
                $found_crss[] = $veda_crs;
            }
        }
        return new ilVedaCourseCollection($found_crss);
    }

    public function getAsynchronusCourses() : ilVedaCourseCollectionInterface
    {
        $assumption_failed_seconds = 5400;
        $diff = time() - $assumption_failed_seconds;
        $found_crss = [];
        foreach ($this->veda_crss as $veda_crs) {
            if (
                $veda_crs->getModified() < $diff &&
                $veda_crs->getCreationStatus() === ilVedaCourseStatus::PENDING
            ) {
                $found_crss[] = $veda_crs;
            }
        }
        return new ilVedaCourseCollection($found_crss);
    }

    public function count() : int
    {
        return count($this->veda_crss);
    }

    public function current() : ilVedaCourseInterface
    {
        return $this->veda_crss[$this->index];
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
        return 0 <= $this->index && $this->index < count($this->veda_crss);
    }
}
