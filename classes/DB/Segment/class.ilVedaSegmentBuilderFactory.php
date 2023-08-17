<?php

class ilVedaSegmentBuilderFactory implements ilVedaSegmentBuilderFactoryInterface
{
    protected ilLogger $veda_logger;

    protected ilVedaSegmentRepository $sgmt_repo;

    public function __construct(ilVedaSegmentRepository $sgmt_repo, ilLogger $veda_logger)
    {
        $this->veda_logger = $veda_logger;
        $this->sgmt_repo = $sgmt_repo;
    }

    public function buildSegment(): ilVedaSegmentBuilderInterface
    {
        return new ilVedaSegmentBuilder($this->sgmt_repo, $this->veda_logger);
    }
}