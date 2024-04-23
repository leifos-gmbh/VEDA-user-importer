<?php

use OpenAPI\Client\Model\Lernbegleiterkurszuordnung;

interface ilVedaCourseCompanionCollectionInterface extends Iterator, Countable
{
    public function logContent(ilLogger $logger);

    public function containsCompanionWithOID(string $oid) : bool;

    public function current() : Lernbegleiterkurszuordnung;

    public function key() : int;

    public function next() : void;

    public function rewind() : void;

    public function valid() : bool;

    public function count() : int;
}
