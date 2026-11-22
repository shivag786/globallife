<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An employee's request to correct one day's attendance. */
#[Fillable(['employee_id', 'date', 'check_in_time', 'check_out_time', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'review_note'])]
class AttendanceRegularization extends Model
{
    protected function casts(): array
    {
        return ['date' => 'date', 'reviewed_at' => 'datetime'];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
