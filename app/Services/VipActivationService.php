<?php

namespace App\Services;

use App\Models\CommissionTransaction;
use App\Models\User;
use App\Models\VipMicrosite;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VipActivationService
{
    /**
     * Activate a VIP Member's package: split the plan's joining price three ways
     * (Commission Partner / Branch Manager / Company) and record it permanently.
     *
     * The Branch Manager's commission_percentage is the *total pool* for the branch,
     * not a separate stacked rate:
     *   - Commission Partner share = their own commission_percentage
     *   - Branch Manager share     = their own commission_percentage minus what they
     *                                granted the partner (the unused portion of their cap)
     *   - Company share            = the remainder (100% minus the Branch Manager's cap)
     */
    public function activate(VipMicrosite $microsite, User $activatedBy): CommissionTransaction
    {
        return DB::transaction(function () use ($microsite, $activatedBy) {
            /** @var VipMicrosite $microsite */
            $microsite = VipMicrosite::whereKey($microsite->id)->lockForUpdate()->firstOrFail();

            if ($microsite->isActivated()) {
                throw new RuntimeException('This VIP Member has already been activated.');
            }

            $vipMember = $microsite->user;
            $commissionPartner = $vipMember->creator;
            $branchManager = $commissionPartner?->creator;

            if (! $commissionPartner || ! $branchManager) {
                throw new RuntimeException('This VIP Member is missing a Commission Partner or Branch Manager in its chain.');
            }

            $package = (float) ($microsite->vipPlan->joining_price ?? 0);
            $commissionPartnerPercentage = (float) ($commissionPartner->commission_percentage ?? 0);
            $branchManagerPercentage = (float) ($branchManager->commission_percentage ?? 0);
            $branchManagerEffectivePercentage = max(0, $branchManagerPercentage - $commissionPartnerPercentage);
            $companyPercentage = max(0, 100 - $branchManagerPercentage);

            $commissionPartnerAmount = round($package * $commissionPartnerPercentage / 100, 2);
            $branchManagerAmount = round($package * $branchManagerEffectivePercentage / 100, 2);
            $companyAmount = round($package - $commissionPartnerAmount - $branchManagerAmount, 2);

            $transaction = CommissionTransaction::create([
                'vip_microsite_id' => $microsite->id,
                'vip_plan_id' => $microsite->vip_plan_id,
                'commission_partner_id' => $commissionPartner->id,
                'branch_manager_id' => $branchManager->id,
                'package_amount' => $package,
                'commission_partner_percentage' => $commissionPartnerPercentage,
                'branch_manager_percentage' => $branchManagerPercentage,
                'commission_partner_amount' => $commissionPartnerAmount,
                'branch_manager_amount' => $branchManagerAmount,
                'company_amount' => $companyAmount,
                'activated_by' => $activatedBy->id,
                'activated_at' => now(),
            ]);

            $microsite->update(['activated_at' => now()]);

            return $transaction;
        });
    }
}
