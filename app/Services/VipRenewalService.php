<?php

namespace App\Services;

use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Models\VipRenewal;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Renewal of an expired VIP plan, decided by the owning Commission Partner.
 *
 * Payment happens offline — exactly like first activation, where the partner
 * confirms "payment received". Approving therefore just extends the paid cycle;
 * rejecting records the refusal and leaves the microsite expired (and so showing
 * the maintenance page) until someone approves it later.
 *
 * NOTE: this deliberately records NO commission. The VIP-activation commission
 * (`CommissionTransaction`) is documented as one-per-microsite and is paid on
 * joining only; splitting renewal money is a separate business decision.
 */
class VipRenewalService
{
    /**
     * Approve a renewal onto a chosen package. The package decides the new
     * validity window and the member's product/service caps from here on.
     */
    public function approve(VipMicrosite $microsite, User $actor, VipPlan $plan, ?string $note = null): VipRenewal
    {
        return $this->decide($microsite, $actor, 'approved', $note, $plan);
    }

    public function reject(VipMicrosite $microsite, User $actor, ?string $note = null): VipRenewal
    {
        return $this->decide($microsite, $actor, 'rejected', $note);
    }

    private function decide(VipMicrosite $microsite, User $actor, string $decision, ?string $note, ?VipPlan $plan = null): VipRenewal
    {
        return DB::transaction(function () use ($microsite, $actor, $decision, $note, $plan) {
            /** @var VipMicrosite $microsite */
            $microsite = VipMicrosite::whereKey($microsite->id)->lockForUpdate()->firstOrFail();
            $microsite->load('vipPlan');

            if (! $microsite->isActivated()) {
                throw new RuntimeException('This VIP Member has not been activated yet — activate the plan first.');
            }

            if (! $microsite->isRenewalDue()) {
                throw new RuntimeException(sprintf(
                    'This plan is not due for renewal yet — it can be renewed from %d days before it expires.',
                    VipMicrosite::RENEWAL_WINDOW_DAYS,
                ));
            }

            if ($decision === 'approved' && ! $plan) {
                throw new RuntimeException('Choose a renewal package before approving.');
            }

            if ($plan && $plan->status !== 'active') {
                throw new RuntimeException('That package is no longer available.');
            }

            $previous = $microsite->plan_expires_at;
            $newExpiry = null;

            if ($decision === 'approved') {
                // Measure the new cycle from whichever is later: today, or the
                // expiry they have not reached yet. Renewing late therefore buys a
                // full cycle from today rather than back-dating it, and renewing
                // early stacks on the days still owed instead of forfeiting them.
                $base = $previous && $previous->isFuture() ? $previous->copy() : now();
                $newExpiry = $base->addMonths($plan->validityMonths());

                // The chosen package also becomes their plan, which is what moves
                // their product and service caps.
                $microsite->update([
                    'vip_plan_id' => $plan->id,
                    'plan_expires_at' => $newExpiry,
                ]);
            }

            return VipRenewal::create([
                'vip_microsite_id' => $microsite->id,
                'vip_plan_id' => $plan?->id ?? $microsite->vip_plan_id,
                'decision' => $decision,
                'amount' => $decision === 'approved' ? (float) ($plan->renewal_price ?? 0) : 0,
                'previous_expires_at' => $previous,
                'new_expires_at' => $newExpiry,
                'note' => $note,
                'decided_by' => $actor->id,
                'decided_at' => now(),
            ]);
        });
    }
}
