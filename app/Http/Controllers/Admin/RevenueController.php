<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionTransaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index(Request $request): View
    {
        $query = CommissionTransaction::with('vipMicrosite.city', 'commissionPartner', 'branchManager')
            ->latest('activated_at');

        if ($request->filled('branch_manager_id')) {
            $query->where('branch_manager_id', $request->integer('branch_manager_id'));
        }

        $transactions = $query->paginate(20)->withQueryString();

        $totals = [
            'count' => CommissionTransaction::count(),
            'package' => CommissionTransaction::sum('package_amount'),
            'partners' => CommissionTransaction::sum('commission_partner_amount'),
            'managers' => CommissionTransaction::sum('branch_manager_amount'),
            'company' => CommissionTransaction::sum('company_amount'),
        ];

        $branchManagers = User::role('branch_manager')->orderBy('name')->get();

        return view('admin.revenue.index', [
            'transactions' => $transactions,
            'totals' => $totals,
            'branchManagers' => $branchManagers,
            'selectedBranchManagerId' => $request->integer('branch_manager_id') ?: null,
        ]);
    }
}
