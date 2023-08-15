<?php

class ilVedaMailSegmentType
{
    /**
     * @var int
     */
    public const NONE = 0;
    /**
     * @var int
     */
    public const ERROR = 1;
    /**
     * @var int
     */
    public const USER_UPDATED = 2;
    /**
     * @var int
     */
    public const USER_REMOVED = 3;
    /**
     * @var int
     */
    public const COURSE_UPDATED = 4;
    /**
     * @var int
     */
    public const MEMBERSHIP_UPDATED = 5;

    private function __construct()
    {
    }
}
