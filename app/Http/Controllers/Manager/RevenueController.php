<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CommissionTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class RevenueController extends Controller
{
    public function index(): View
    {
        $transactions = CommissionTransaction::where('commission_partner_id', Auth::id())
            ->with('vipMicrosite.city')
            ->latest('activated_at')
            ->paginate(20);

        $totals = [
            'count' => CommissionTransaction::where('commission_partner_id', Auth::id())->count(),
            'earned' => CommissionTransaction::where('commission_partner_id', Auth::id())->sum('commission_partner_amount'),
        ];

        return view('manager.revenue.index', ['transactions' => $transactions, 'totals' => $totals]);
    }
}
