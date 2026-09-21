<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'vip_microsite_id', 'vip_plan_id', 'decision', 'amount',
    'previous_expires_at', 'new_expires_at', 'note', 'decided_by', 'decided_at',
])]
class VipRenewal extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'previous_expires_at' => 'datetime',
            'new_expires_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function isApproved(): bool
    {
        return $this->decision === 'approved';
    }

    /**
     * @return BelongsTo<VipMicrosite, $this>
     */
    public function vipMicrosite(): BelongsTo
    {
        return $this->belongsTo(VipMicrosite::class);
    }

    /**
     * @return BelongsTo<VipPlan, $this>
     */
    public function vipPlan(): BelongsTo
    {
        return $this->belongsTo(VipPlan::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
