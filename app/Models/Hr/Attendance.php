<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'employee_id', 'date', 'check_in_at', 'check_out_at', 'status', 'is_late', 'worked_minutes',
    'check_in_ip', 'check_out_ip', 'note', 'source', 'updated_by',
])]
class Attendance extends Model
{
    use LogsActivity;

    public const STATUSES = ['present' => 'Present', 'half_day' => 'Half Day', 'absent' => 'Absent'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'is_late' => 'boolean',
            'worked_minutes' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['check_in_at', 'check_out_at', 'status'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function workedLabel(): string
    {
        return $this->worked_minutes > 0
            ? intdiv($this->worked_minutes, 60).'h '.str_pad((string) ($this->worked_minutes % 60), 2, '0', STR_PAD_LEFT).'m'
            : '-';
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
