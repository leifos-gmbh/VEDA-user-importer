<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilVedaUtils
 */
class ilVedaUtils
{
    /**
     * Compare two oid (case insensitive)
     */
    public static function compareOidsEqual(string $first = null, string $second = null) :bool
    {
        return strcmp(
            strtolower($first),
            strtolower($second)
        ) === 0;
    }
}