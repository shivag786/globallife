<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['employee_id', 'title', 'category', 'amount', 'expense_date', 'description', 'receipt_path', 'status', 'reviewed_by', 'reviewed_at', 'review_note', 'paid_at'])]
class ExpenseClaim extends Model
{
    use LogsActivity;

    public const CATEGORIES = ['Travel', 'Food', 'Fuel', 'Accommodation', 'Office Supplies', 'Client Meeting', 'Mobile / Internet', 'Other'];

    public const STATUSES = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'paid' => 'Paid'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'expense_date' => 'date', 'reviewed_at' => 'datetime', 'paid_at' => 'datetime'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'amount', 'review_note'])->logOnlyDirty()->dontSubmitEmptyLogs();
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
