<?php

class ilVedaMailSegmentBuilder implements ilVedaMailSegmentBuilderInterface
{
    protected ilVedaMailSegmentInterface $mail_segment;
    protected ilVedaMailSegmentRepositoryInterface $mail_segment_repo;

    public function __construct(ilVedaMailSegmentRepositoryInterface $mail_segment_repo) {
        $this->mail_segment_repo = $mail_segment_repo;
        $this->mail_segment = new ilVedaMailSegment(
            -1,
            '',
            ilVedaMailSegmentType::NONE,
            new DateTimeImmutable('now', new DateTimeZone('Utc'))
        );
    }

    public function withType(string $type): ilVedaMailSegmentBuilderInterface
    {
        $builder =  new ilVedaMailSegmentBuilder($this->mail_segment_repo);
        $builder->mail_segment = $this->mail_segment;
        $builder->mail_segment->setType($type);
        return $builder;
    }

    public function withMessage(string $message): ilVedaMailSegmentBuilderInterface
    {
        $builder =  new ilVedaMailSegmentBuilder($this->mail_segment_repo);
        $builder->mail_segment = $this->mail_segment;
        $builder->mail_segment->setMessage($message);
        return $builder;
    }

    public function store(): void
    {
        $this->mail_segment_repo->addMailSegment($this->mail_segment);
    }
}