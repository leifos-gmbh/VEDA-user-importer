<?php

class ilVedaCourseStatus
{
    /**
     * @var int
     */
    public const NONE = 0;
    /**
     * @var int
     */
    public const PENDING = 1;
    /**
     * @var int
     */
    public const SYNCHRONIZED = 2;
    /**
     * @var int
     */
    public const FAILED = 3;

    private function __construct()
    {
    }
}
