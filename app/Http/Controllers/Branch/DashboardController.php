<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\CommissionTransaction;
use App\Support\ChartData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $manager = Auth::user()->load('branchCities');
        $base = CommissionTransaction::where('branch_manager_id', $manager->id);

        $byPartner = (clone $base)
            ->selectRaw('commission_partner_id, SUM(branch_manager_amount) as earned')
            ->groupBy('commission_partner_id')
            ->with('commissionPartner')
            ->get();

        return view('dashboards.branch-manager', [
            'manager' => $manager,
            'partnerCount' => $manager->commissionPartners()->count(),
            'stats' => [
                'earned' => (float) (clone $base)->sum('branch_manager_amount'),
                'activations' => (clone $base)->count(),
            ],
            'revenueChart' => ChartData::monthly(clone $base, 'activated_at', 'SUM(branch_manager_amount)'),
            'partnerChart' => [
                'categories' => $byPartner->map(fn ($row) => $row->commissionPartner->name ?? '—')->all(),
                'data' => $byPartner->map(fn ($row) => round((float) $row->earned, 2))->all(),
            ],
        ]);
    }
}
