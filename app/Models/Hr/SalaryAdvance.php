<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** An advance / loan recovered from salary in monthly instalments. */
#[Fillable(['employee_id', 'amount', 'monthly_deduction', 'balance', 'reason', 'issued_on', 'status', 'created_by'])]
class SalaryAdvance extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return ['amount' => 'float', 'monthly_deduction' => 'float', 'balance' => 'float', 'issued_on' => 'date'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['balance', 'status'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
