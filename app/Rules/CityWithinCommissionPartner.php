<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CityWithinCommissionPartner implements ValidationRule
{
    /**
     * @param  list<int>  $allowedCityIds
     */
    public function __construct(private readonly array $allowedCityIds)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! in_array((int) $value, $this->allowedCityIds, true)) {
            $fail('You can only register VIP Members in cities you serve.');
        }
    }
}
