<?php

use OpenAPI\Client\Model\Dozentenkurszuordnung;

interface ilVedaCourseTutorsCollectionInterface extends Iterator, Countable
{
    public function logContent(ilLogger $logger);

    public function containsTutorWithOID(string $oid) : bool;

    public function current() : Dozentenkurszuordnung;

    public function key() : int;

    public function next() : void;

    public function rewind() : void;

    public function valid() : bool;

    public function count() : int;
}
