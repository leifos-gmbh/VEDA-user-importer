<?php

use OpenAPI\Client\Model\Elearningkurs;

class ilVedaELearningCourseCollection implements ilVedaELearningCourseCollectionInterface
{
    /**
     * @var Elearningkurs[]
     */
    protected array $elearning_courses;
    protected int $index;

    /**
     * @param Elearningkurs[] $elearning_courses
     */
    public function __construct(array $elearning_courses)
    {
        $this->elearning_courses = $elearning_courses;
        $this->index = 0;
    }

    public function current() : Elearningkurs
    {
        return $this->elearning_courses[$this->index];
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
        return 0 <= $this->index && $this->index < count($this->elearning_courses);
    }

    public function count() : int
    {
        return count($this->elearning_courses);
    }
}
