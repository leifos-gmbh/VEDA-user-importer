<?php

interface ilVedaMailSegmentCollectionInterface extends Iterator, Countable
{
    public function getMailSegmentsWithType(string $type) : ilVedaMailSegmentCollectionInterface;

    public function getMailSegmentsInDateRange(
        DateTimeImmutable $from,
        DateTimeImmutable $to
    ) : ilVedaMailSegmentCollectionInterface;

    public function next() : void;

    public function count() : int;

    public function current() : ilVedaMailSegmentInterface;

    public function key() : int;

    public function rewind() : void;

    public function valid() : bool;
}
