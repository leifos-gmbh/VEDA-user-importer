<?php

interface ilVedaMailSegmentInterface
{
    public function getID() : int;

    public function getMessage() : string;

    public function setMessage(string $message) : void;

    public function getType() : string;

    public function setType(string $type) : void;

    public function getLastModified() : DateTimeImmutable;
}
