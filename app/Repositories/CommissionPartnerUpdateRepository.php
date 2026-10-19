<?php

namespace App\Repositories;

use App\Models\CommissionPartnerUpdate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reads for Super Admin's notes about Commission Partner changes.
 */
class CommissionPartnerUpdateRepository
{
    /**
     * A Branch Manager's unread notes, newest first, for their dashboard.
     *
     * @return Collection<int, CommissionPartnerUpdate>
     */
    public function unreadFor(User $branchManager, int $limit = 10): Collection
    {
        return CommissionPartnerUpdate::where('branch_manager_id', $branchManager->id)
            ->unread()
            ->with(['partner', 'updatedBy'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function unreadCountFor(User $branchManager): int
    {
        return CommissionPartnerUpdate::where('branch_manager_id', $branchManager->id)->unread()->count();
    }

    /**
     * Unread note counts keyed by partner id, for badges on the partner list.
     *
     * @return array<int, int>
     */
    public function unreadCountsByPartner(User $branchManager): array
    {
        return CommissionPartnerUpdate::where('branch_manager_id', $branchManager->id)
            ->unread()
            ->selectRaw('commission_partner_id, COUNT(*) as total')
            ->groupBy('commission_partner_id')
            ->pluck('total', 'commission_partner_id')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /**
     * Every note about one partner, newest first.
     *
     * @return Collection<int, CommissionPartnerUpdate>
     */
    public function historyFor(User $partner): Collection
    {
        return CommissionPartnerUpdate::where('commission_partner_id', $partner->id)
            ->with(['updatedBy', 'partner'])
            ->latest()
            ->get();
    }
}
