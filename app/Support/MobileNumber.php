<?php

namespace App\Support;

/**
 * Indian mobile numbers, stored as exactly 10 digits.
 *
 * Input arrives in every shape a browser can produce — typed, pasted off a
 * WhatsApp chat, or autofilled from a contact card — so "+91 98765 43210",
 * "09876543210" and "+91-98765-43210" all have to land as "9876543210".
 * Taking the LAST 10 digits handles every country-code and trunk-prefix form
 * without needing a list of prefixes to strip.
 */
final class MobileNumber
{
    public const LENGTH = 10;

    /**
     * Reduce any input to its last 10 digits, or null when there is nothing.
     *
     * Shorter input is returned as-is rather than padded, so validation can
     * report it as too short instead of silently accepting a wrong number.
     */
    public static function normalise(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return null;
        }

        return strlen($digits) > self::LENGTH
            ? substr($digits, -self::LENGTH)
            : $digits;
    }
}
