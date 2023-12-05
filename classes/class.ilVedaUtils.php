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
    public static function compareOidsEqual(string $first = null, string $second = null) : bool
    {
        return strcmp(
            strtolower($first),
            strtolower($second)
        ) === 0;
    }

    public static function isValidDate(?DateTime $start, ?DateTime $end) : bool
    {
        global $DIC;

        $logger = ilVedaConnectorPlugin::getInstance()->getLogger();

        if ($start == null && $end == null) {
            return true;
        }

        $now = new \ilDate(time(), IL_CAL_UNIX);
        if ($start == null) {
            $ilend = new \ilDateTime($end->format('Y-m-d'), IL_CAL_DATE);
            // check ending time > now
            if (
                \ilDateTime::_after($ilend, $now, IL_CAL_DAY) ||
                \ilDateTime::_equals($ilend, $now, IL_CAL_DAY)
            ) {
                $logger->debug('Ending date is valid');
                return true;
            }
            $logger->debug('Ending date is invalid');
            return false;
        }

        if ($end == null) {
            $ilstart = new \ilDate($start->format('Y-m-d'), IL_CAL_DATE);
            // check starting time <= now
            if (
                \ilDateTime::_before($ilstart, $now, IL_CAL_DAY) ||
                \ilDateTime::_equals($ilstart, $now, IL_CAL_DAY)
            ) {
                $logger->debug('Starting date is valid');
                return true;
            }
            $logger->debug('Starting date is invalid');
            return false;
        }

        $ilstart = new \ilDate($start->format('Y-m-d'), IL_CAL_DATE);
        $ilend = new \ilDate($end->format('Y-m-d'), IL_CAL_DATE);

        if (
            \ilDateTime::_within(
                $now,
                $ilstart,
                $ilend,
                IL_CAL_DAY
            ) ||
            \ilDateTime::_equals(
                $now,
                $ilend,
                \ilDateTime::DAY
            )
        ) {
            return true;
        }
        return false;
    }
}
