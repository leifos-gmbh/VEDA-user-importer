<?php

interface ilVedaMailSegmentRepositoryInterface
{
    public function lookupMailSegments() : ilVedaMailSegmentCollectionInterface;

    public function addMailSegment(ilVedaMailSegmentInterface $mail_segment) : void;

    public function deleteMailSegment(ilVedaMailSegmentInterface $mail_segment) : void;

    public function deleteAllMailSegments() : void;
}
