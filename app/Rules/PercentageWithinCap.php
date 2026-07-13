<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PercentageWithinCap implements ValidationRule
{
    public function __construct(private readonly float $cap)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((float) $value > $this->cap) {
            $fail("The {$attribute} must not exceed {$this->cap}%.");
        }
    }
}
