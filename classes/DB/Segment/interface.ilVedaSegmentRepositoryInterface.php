<?php

interface ilVedaSegmentRepositoryInterface
{
    public function updateSegmentInfo(ilVedaSegmentInterface $veda_sgmt) : void;

    public function deleteSegmentInfo(string $oid) : void;

    public function lookupSegmentInfo(string $oid) : ?ilVedaSegmentInterface;
}
