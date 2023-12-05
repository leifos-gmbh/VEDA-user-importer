<?php

use OpenAPI\Client\Model\AusbildungszugDozent;

interface ilVedaEducationTrainTutorCollectionInterface extends Iterator, Countable
{
    public function logContent(ilLogger $logger) : void;

    public function current() : AusbildungszugDozent;

    public function key() : int;

    public function next() : void;

    public function rewind() : void;

    public function valid() : bool;

    public function count() : int;
}
