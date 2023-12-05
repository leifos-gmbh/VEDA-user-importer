<?php

use OpenAPI\Client\Model\Teilnehmerkurszuordnung;

interface ilVedaCourseMemberCollectionInterface extends Iterator, Countable
{
    public function logContent(ilLogger $logger);

    public function containsMemberWithOID(string $oid) : bool;

    public function current() : Teilnehmerkurszuordnung;

    public function key() : int;

    public function next() : void;

    public function rewind() : void;

    public function valid() : bool;

    public function count() : int;
}
