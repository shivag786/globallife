<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A 12-digit Aadhaar number whose last digit is a valid Verhoeff check digit.
 *
 * Every real Aadhaar number carries one, so this catches most single-digit typos
 * and transposed pairs at the form instead of in a KYC check later. It does not
 * (and cannot) confirm the number belongs to anyone.
 */
class AadhaarNumber implements ValidationRule
{
    private const D = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 2, 3, 4, 0, 6, 7, 8, 9, 5],
        [2, 3, 4, 0, 1, 7, 8, 9, 5, 6], [3, 4, 0, 1, 2, 8, 9, 5, 6, 7],
        [4, 0, 1, 2, 3, 9, 5, 6, 7, 8], [5, 9, 8, 7, 6, 0, 4, 3, 2, 1],
        [6, 5, 9, 8, 7, 1, 0, 4, 3, 2], [7, 6, 5, 9, 8, 2, 1, 0, 4, 3],
        [8, 7, 6, 5, 9, 3, 2, 1, 0, 4], [9, 8, 7, 6, 5, 4, 3, 2, 1, 0],
    ];

    private const P = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 5, 7, 6, 2, 8, 3, 0, 9, 4],
        [5, 8, 0, 3, 7, 9, 6, 1, 4, 2], [8, 9, 1, 6, 0, 4, 3, 5, 2, 7],
        [9, 4, 5, 3, 1, 2, 6, 8, 7, 0], [4, 2, 8, 6, 5, 7, 3, 9, 0, 1],
        [2, 7, 9, 3, 8, 0, 6, 4, 1, 5], [7, 0, 4, 6, 9, 1, 3, 2, 5, 8],
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if (! preg_match('/^[2-9]\d{11}$/', $digits)) {
            $fail('The Aadhaar number must be 12 digits and cannot start with 0 or 1.');

            return;
        }

        if (! self::verhoeffValid($digits)) {
            $fail('That Aadhaar number is not valid. Please check it for a mistyped digit.');
        }
    }

    private const INV = [0, 4, 3, 2, 1, 5, 6, 7, 8, 9];

    /** The Verhoeff check digit for an 11-digit prefix (used to build test data). */
    public static function checkDigit(string $prefix): int
    {
        $c = 0;
        $reversed = strrev($prefix);

        for ($i = 0, $n = strlen($reversed); $i < $n; $i++) {
            $c = self::D[$c][self::P[($i + 1) % 8][(int) $reversed[$i]]];
        }

        return self::INV[$c];
    }

    public static function verhoeffValid(string $digits): bool
    {
        $c = 0;
        $reversed = strrev($digits);

        for ($i = 0, $n = strlen($reversed); $i < $n; $i++) {
            $c = self::D[$c][self::P[$i % 8][(int) $reversed[$i]]];
        }

        return $c === 0;
    }
}
