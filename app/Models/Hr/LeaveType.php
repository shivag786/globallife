<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A leave category. `days_per_year` = 0 means no quota is tracked (e.g. unpaid
 * leave can be taken without a balance).
 */
#[Fillable(['name', 'code', 'days_per_year', 'is_paid', 'carry_forward', 'max_carry_forward', 'is_active'])]
class LeaveType extends Model
{
    protected function casts(): array
    {
        return [
            'days_per_year' => 'float',
            'max_carry_forward' => 'float',
            'is_paid' => 'boolean',
            'carry_forward' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function hasQuota(): bool
    {
        return $this->days_per_year > 0;
    }
}
