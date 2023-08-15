<?php

class ilVedaMailSegmentCollection implements ilVedaMailSegmentCollectionInterface
{
    /**
     * @var ilVedaMailSegmentInterface[]
     */
    protected array $mail_segments;
    protected int $index;

    /**
     * @param ilVedaMailSegmentInterface[] $mail_segments
     */
    public function __construct(
        array $mail_segments
    ) {
        $this->mail_segments = $mail_segments;
        $this->index = 0;
    }

    public function current() : ilVedaMailSegmentInterface
    {
        return $this->mail_segments[$this->index];
    }

    public function next() : void
    {
        $this->index++;
    }

    public function key() : int
    {
        return $this->index;
    }

    public function valid() : bool
    {
        return 0 <= $this->index && $this->index < count($this->mail_segments);
    }

    public function rewind() : void
    {
        $this->index = 0;
    }

    public function getMailSegmentsWithType(string $type) : ilVedaMailSegmentCollectionInterface
    {
        $mail_segments = [];
        foreach ($this as $mail_segment) {
            if ($mail_segment->getType() === $type) {
                $mail_segments[] = $mail_segment;
            }
        }
        return new ilVedaMailSegmentCollection($mail_segments);
    }

    public function getMailSegmentsInDateRange(
        DateTimeImmutable $from,
        DateTimeImmutable $to
    ) : ilVedaMailSegmentCollectionInterface {
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }
        $mail_segments = [];
        foreach ($this as $mail_segment) {
            if (
                $to <= $mail_segment->getLastModified() &&
                $mail_segment->getLastModified() <= $from
            ) {
                $mail_segments[] = $mail_segment;
            }
        }
        return new ilVedaMailSegmentCollection($mail_segments);
    }

    public function count() : int
    {
        return count($this->mail_segments);
    }
}
