<?php

namespace App\Http\Controllers;

use App\Models\CommissionEarning;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    /**
     * A user's product-commission wallet: balances + earning history. Shared by
     * VIP members, Commission Partners and Branch Managers (each sees only their own).
     */
    public function index(WithdrawalService $withdrawals): View
    {
        $user = Auth::user();
        $isVipMember = $user->hasRole('vip_member');

        $agg = CommissionEarning::where('beneficiary_id', $user->id)
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN status='pending' THEN amount END),0) as pending,
                 COALESCE(SUM(CASE WHEN status='approved' THEN amount END),0) as lifetime,
                 COALESCE(SUM(CASE WHEN status='approved' AND approved_at >= ? THEN amount END),0) as today,
                 COALESCE(SUM(CASE WHEN status='approved' AND approved_at >= ? THEN amount END),0) as monthly,
                 COALESCE(SUM(base_amount),0) as total_sales,
                 COUNT(*) as entries",
                [now()->startOfDay(), now()->startOfMonth()]
            )
            ->first();

        return view('wallet.index', [
            'summary' => [
                'balance' => (float) ($user->wallet?->balance ?? 0),
                'pending' => (float) $agg->pending,
                'lifetime' => (float) $agg->lifetime,
                'today' => (float) $agg->today,
                'monthly' => (float) $agg->monthly,
                'total_sales' => (float) $agg->total_sales,
                'entries' => (int) $agg->entries,
            ],
            'transactions' => CommissionEarning::with(['order', 'product'])
                ->where('beneficiary_id', $user->id)
                ->latest()
                ->paginate(20),

            // Withdrawals are a VIP-member feature; partners are settled monthly
            // by the Super Admin through admin/partner-payouts instead.
            'isVipMember' => $isVipMember,
            'withdrawable' => $isVipMember ? $withdrawals->availableBalance($user) : 0.0,
            'withdrawBlockReason' => $isVipMember ? $withdrawals->blockReason($user) : null,
            // Keep the form (and its always-enabled button) up when a request is
            // already open, but hide it when the balance simply is not there.
            'showWithdrawForm' => $isVipMember && (
                $withdrawals->availableBalance($user) >= WithdrawalService::MINIMUM_AMOUNT
                || $withdrawals->hasOpenRequest($user)
            ),
            'minimumWithdrawal' => WithdrawalService::MINIMUM_AMOUNT,
            'defaultWithdrawAmount' => $isVipMember && $withdrawals->availableBalance($user) >= WithdrawalService::MINIMUM_AMOUNT
                ? number_format($withdrawals->availableBalance($user), 2, '.', '')
                : (string) WithdrawalService::MINIMUM_AMOUNT,
            'withdrawals' => $isVipMember
                ? WithdrawalRequest::where('user_id', $user->id)->latest('created_at')->get()
                : collect(),
        ]);
    }
}
