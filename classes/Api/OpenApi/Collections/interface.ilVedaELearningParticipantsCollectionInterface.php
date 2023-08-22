<?php

use OpenAPI\Client\Model\TeilnehmerELearningPlattform;

interface ilVedaELearningParticipantsCollectionInterface extends Iterator, Countable
{
    public function current() : TeilnehmerELearningPlattform;

    public function key() : int;

    public function next() : void;

    public function rewind() : void;

    public function valid() : bool;

    public function count() : int;
}