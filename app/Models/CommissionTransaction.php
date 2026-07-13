<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'vip_microsite_id', 'vip_plan_id', 'commission_partner_id', 'branch_manager_id',
    'package_amount', 'commission_partner_percentage', 'branch_manager_percentage',
    'commission_partner_amount', 'branch_manager_amount', 'company_amount',
    'activated_by', 'activated_at',
])]
class CommissionTransaction extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
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
    public function commissionPartner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commission_partner_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function branchManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'branch_manager_id');
    }
}
