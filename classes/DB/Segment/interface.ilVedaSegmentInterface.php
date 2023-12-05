<?php

interface ilVedaSegmentInterface
{
    public function setOID(string $oid) : void;

    public function getOID() : string;

    public function setType(string $type) : void;

    public function getType() : string;

    public function isPracticalTraining() : bool;

    public function isSelfLearning() : bool;
}
