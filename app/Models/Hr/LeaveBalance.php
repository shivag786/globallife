<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'leave_type_id', 'year', 'allotted', 'carried_forward', 'used'])]
class LeaveBalance extends Model
{
    protected function casts(): array
    {
        return ['allotted' => 'float', 'carried_forward' => 'float', 'used' => 'float', 'year' => 'integer'];
    }

    public function total(): float
    {
        return $this->allotted + $this->carried_forward;
    }

    public function remaining(): float
    {
        return max(0, $this->total() - $this->used);
    }

    /** @return BelongsTo<LeaveType, $this> */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
