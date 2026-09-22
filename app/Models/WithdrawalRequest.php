<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'user_id', 'amount', 'status', 'wallet_balance_at_request',
    'utr_number', 'payment_screenshot_path', 'admin_note', 'paid_by', 'paid_at',
])]
class WithdrawalRequest extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'wallet_balance_at_request' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Whether there is something for the member to look at. Marking paid always
     * captures at least one of these, so in practice this is true once paid.
     */
    public function hasProof(): bool
    {
        return filled($this->utr_number) || filled($this->payment_screenshot_path);
    }

    public function screenshotUrl(): ?string
    {
        return filled($this->payment_screenshot_path)
            ? asset('storage/'.$this->payment_screenshot_path)
            : null;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
