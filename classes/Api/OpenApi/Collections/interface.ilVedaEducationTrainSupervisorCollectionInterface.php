<?php

use OpenAPI\Client\Model\AufsichtspersonKurszugriff;

interface ilVedaEducationTrainSupervisorCollectionInterface extends Iterator, Countable
{
    public function logContent(ilLogger $logger) : void;

    public function current() : AufsichtspersonKurszugriff;

    public function key() : int;

    public function next() : void;

    public function rewind() : void;

    public function valid() : bool;

    public function count() : int;
}
