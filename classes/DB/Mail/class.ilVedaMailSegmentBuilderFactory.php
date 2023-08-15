<?php

class ilVedaMailSegmentBuilderFactory implements ilVedaMailSegmentBuilderFactoryInterface
{
    protected ilVedaMailSegmentRepositoryInterface $mail_segment_repo;

    public function __construct(ilVedaMailSegmentRepositoryInterface $mail_segment_repo)
    {
        $this->mail_segment_repo = $mail_segment_repo;
    }

    public function buildSegment(): ilVedaMailSegmentBuilderInterface
    {
        return new ilVedaMailSegmentBuilder($this->mail_segment_repo);
    }
}