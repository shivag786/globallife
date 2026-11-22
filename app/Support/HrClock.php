<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Office time for the HR module.
 *
 * The app runs in UTC, but attendance is about the wall clock in India: a
 * 10:00 shift start, "today", a late mark. HR date-times are therefore
 * stored and compared as IST wall-clock values (a check-in at 10:05 IST is
 * saved as "10:05:00"), and every "now" / "today" in HR code comes from here
 * so the comparisons never mix UTC and IST.
 */
final class HrClock
{
    public const TZ = 'Asia/Kolkata';

    public static function now(): Carbon
    {
        return Carbon::parse(Carbon::now(self::TZ)->format('Y-m-d H:i:s'));
    }

    public static function today(): Carbon
    {
        return self::now()->startOfDay();
    }
}
