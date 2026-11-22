<?php

namespace App\Models\Hr;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A working-hours rule. `weekly_offs` holds Carbon dayOfWeek numbers
 * (0 = Sunday … 6 = Saturday).
 */
#[Fillable(['name', 'start_time', 'end_time', 'grace_minutes', 'half_day_minutes', 'full_day_minutes', 'weekly_offs', 'is_default'])]
class Shift extends Model
{
    public const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    protected function casts(): array
    {
        return [
            'weekly_offs' => 'array',
            'is_default' => 'boolean',
            'grace_minutes' => 'integer',
            'half_day_minutes' => 'integer',
            'full_day_minutes' => 'integer',
        ];
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->first() ?? static::orderBy('id')->first();
    }

    public function isWeeklyOff(CarbonInterface $date): bool
    {
        return in_array($date->dayOfWeek, array_map('intval', $this->weekly_offs ?? []), true);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Employee, $this> */
    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function label(): string
    {
        return $this->name.' ('.substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5).')';
    }
}
