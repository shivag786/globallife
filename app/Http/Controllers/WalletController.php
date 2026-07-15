<?php

namespace App\Http\Controllers;

use App\Models\CommissionEarning;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    /**
     * A user's product-commission wallet: balances + earning history. Shared by
     * VIP members, Commission Partners and Branch Managers (each sees only their own).
     */
    public function index(): View
    {
        $user = Auth::user();

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
        ]);
    }
}
