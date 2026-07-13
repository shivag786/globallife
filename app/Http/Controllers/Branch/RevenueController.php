<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\CommissionTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class RevenueController extends Controller
{
    public function index(): View
    {
        $branchManagerId = Auth::id();

        $transactions = CommissionTransaction::where('branch_manager_id', $branchManagerId)
            ->with('vipMicrosite.city', 'commissionPartner')
            ->latest('activated_at')
            ->paginate(20);

        $totals = [
            'count' => CommissionTransaction::where('branch_manager_id', $branchManagerId)->count(),
            'earned' => CommissionTransaction::where('branch_manager_id', $branchManagerId)->sum('branch_manager_amount'),
        ];

        $byPartner = CommissionTransaction::where('branch_manager_id', $branchManagerId)
            ->selectRaw('commission_partner_id, COUNT(*) as activations, SUM(commission_partner_amount) as partner_earned, SUM(branch_manager_amount) as your_earned')
            ->with('commissionPartner')
            ->groupBy('commission_partner_id')
            ->get();

        return view('branch.revenue.index', ['transactions' => $transactions, 'totals' => $totals, 'byPartner' => $byPartner]);
    }
}
