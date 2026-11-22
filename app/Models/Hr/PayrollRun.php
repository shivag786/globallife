<?php

namespace App\Models\Hr;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** One month's payroll: `draft` while it can be recalculated, then `finalized`. */
#[Fillable(['month', 'status', 'created_by', 'finalized_by', 'finalized_at'])]
class PayrollRun extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return ['finalized_at' => 'datetime'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['month', 'status'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function start(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->month.'-01')->startOfDay();
    }

    public function label(): string
    {
        return $this->start()->format('F Y');
    }

    /** @return HasMany<Payslip, $this> */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    /** @return BelongsTo<User, $this> */
    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }
}
