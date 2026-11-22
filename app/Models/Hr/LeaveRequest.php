<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['employee_id', 'leave_type_id', 'from_date', 'to_date', 'is_half_day', 'days', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'review_note'])]
class LeaveRequest extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'is_half_day' => 'boolean',
            'days' => 'float',
            'reviewed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'review_note'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function periodLabel(): string
    {
        $label = $this->from_date->format('d M Y');

        if (! $this->from_date->equalTo($this->to_date)) {
            $label .= ' - '.$this->to_date->format('d M Y');
        }

        return $this->is_half_day ? $label.' (half day)' : $label;
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<LeaveType, $this> */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
