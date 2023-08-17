<?php

class ilVedaMailSegmentBuilder implements ilVedaMailSegmentBuilderInterface
{
    protected ilVedaMailSegmentInterface $mail_segment;
    protected ilVedaMailSegmentRepositoryInterface $mail_segment_repo;
    protected ilLogger $veda_logger;

    public function __construct(
        ilVedaMailSegmentRepositoryInterface $mail_segment_repo,
        ilLogger $veda_logger
    ) {
        $this->mail_segment_repo = $mail_segment_repo;
        $this->mail_segment = new ilVedaMailSegment(
            -1,
            '',
            ilVedaMailSegmentType::NONE,
            new DateTimeImmutable('now', new DateTimeZone('Utc'))
        );
        $this->veda_logger = $veda_logger;
    }

    public function withType(string $type) : ilVedaMailSegmentBuilderInterface
    {
        $this->veda_logger->debug('Adding type: "' . $type . '", to mail segment with id: '
            . $this->mail_segment->getID()
        );
        $builder = new ilVedaMailSegmentBuilder($this->mail_segment_repo, $this->veda_logger);
        $builder->mail_segment = $this->mail_segment;
        $builder->mail_segment->setType($type);
        return $builder;
    }

    public function withMessage(string $message) : ilVedaMailSegmentBuilderInterface
    {
        $this->veda_logger->debug('Adding message: "'. $message .'", to mail segment with id: '
            . $this->mail_segment->getID()
        );
        $builder = new ilVedaMailSegmentBuilder($this->mail_segment_repo, $this->veda_logger);
        $builder->mail_segment = $this->mail_segment;
        $builder->mail_segment->setMessage($message);
        return $builder;
    }

    public function store() : void
    {
        $this->veda_logger->debug('Storing mail segment with id: ' . $this->mail_segment->getID());
        $this->mail_segment_repo->addMailSegment($this->mail_segment);
    }
}
