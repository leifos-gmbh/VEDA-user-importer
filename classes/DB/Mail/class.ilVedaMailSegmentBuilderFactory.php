<?php

class ilVedaMailSegmentBuilderFactory implements ilVedaMailSegmentBuilderFactoryInterface
{
    protected ilVedaMailSegmentRepositoryInterface $mail_segment_repo;
    protected ilLogger $veda_logger;

    public function __construct(ilVedaMailSegmentRepositoryInterface $mail_segment_repo, ilLogger $veda_logger)
    {
        $this->mail_segment_repo = $mail_segment_repo;
        $this->veda_logger = $veda_logger;
    }

    public function buildSegment() : ilVedaMailSegmentBuilderInterface
    {
        return new ilVedaMailSegmentBuilder($this->mail_segment_repo, $this->veda_logger);
    }
}
