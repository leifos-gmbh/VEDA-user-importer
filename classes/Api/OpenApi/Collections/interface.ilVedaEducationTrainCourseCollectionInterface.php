<?php

use OpenAPI\Client\Model\Ausbildungszug;

interface ilVedaEducationTrainCourseCollectionInterface extends Iterator, Countable
{
    public function getByOID(string $oid) : ?Ausbildungszug;

    public function current() : Ausbildungszug;

    public function key() : int;

    public function next() : void;

    public function rewind() : void;

    public function valid() : bool;

    public function count() : int;
}