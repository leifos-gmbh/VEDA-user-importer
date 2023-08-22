<?php

use OpenAPI\Client\Model\AusbildungszugTeilnehmer;

interface ilVedaEducationTrainMemberCollectionInterface extends Iterator, Countable
{
    public function logContent(ilLogger $logger) : void;

    public function current() : AusbildungszugTeilnehmer;

    public function key() : int;

    public function next() : void;

    public function rewind() : void;

    public function valid() : bool;

    public function count() : int;
}