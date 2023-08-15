<?php

interface ilVedaSegmentRepositoryInterface
{
    public function updateSegmentInfo(ilVedaSegmentInterface $segment_status) : void;

    public function deleteSegmentInfo(string $oid) : void;

    public function lookupSegmentInfo(string $oid) : ?ilVedaSegmentInterface;

    public function createEmptySegment(string $oid) : ilVedaSegment;
}
