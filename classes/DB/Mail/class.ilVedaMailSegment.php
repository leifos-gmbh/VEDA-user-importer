<?php

class ilVedaMailSegment implements ilVedaMailSegmentInterface
{
    protected int $id;
    protected string $message;
    protected string $type;
    protected DateTimeImmutable $modified;

    public function __construct(
        int $id,
        string $message,
        string $type,
        DateTimeImmutable $modified
    ) {
        $this->id = $id;
        $this->message = $message;
        $this->type = $type;
        $this->modified = $modified;
    }

    public function getID() : int
    {
        return $this->id;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getLastModified() : DateTimeImmutable
    {
        return $this->modified;
    }

    public function setMessage(string $message) : void
    {
        $this->message = $message;
    }

    public function setType(string $type) : void
    {
        $this->type = $type;
    }
}
