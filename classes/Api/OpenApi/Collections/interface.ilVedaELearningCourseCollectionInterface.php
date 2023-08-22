<?php

use OpenAPI\Client\Model\Elearningkurs;

interface ilVedaELearningCourseCollectionInterface extends Iterator, Countable
{
    public function current() : ELearningkurs;

    public function key() : int;

    public function next() : void;

    public function rewind() : void;

    public function valid() : bool;

    public function count() : int;
}