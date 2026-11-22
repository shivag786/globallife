<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** Monthly salary structure of one employee. */
#[Fillable(['employee_id', 'basic', 'hra', 'conveyance', 'special_allowance', 'other_allowance', 'pf_enabled', 'esi_enabled', 'professional_tax', 'tds'])]
class EmployeeSalary extends Model
{
    use LogsActivity;

    public const EARNINGS = [
        'basic' => 'Basic',
        'hra' => 'HRA',
        'conveyance' => 'Conveyance',
        'special_allowance' => 'Special Allowance',
        'other_allowance' => 'Other Allowance',
    ];

    protected function casts(): array
    {
        return [
            'basic' => 'float', 'hra' => 'float', 'conveyance' => 'float',
            'special_allowance' => 'float', 'other_allowance' => 'float',
            'professional_tax' => 'float', 'tds' => 'float',
            'pf_enabled' => 'boolean', 'esi_enabled' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function gross(): float
    {
        return round($this->basic + $this->hra + $this->conveyance + $this->special_allowance + $this->other_allowance, 2);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
