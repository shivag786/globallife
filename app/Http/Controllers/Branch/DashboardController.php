<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\CommissionEarning;
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

        // Two income streams: VIP-plan activations and product-sale commission.
        $vipRevenue = (float) (clone $base)->sum('branch_manager_amount');
        $product = CommissionEarning::where('beneficiary_id', $manager->id);
        $productRevenue = (float) (clone $product)->where('status', 'approved')->sum('amount');
        $productPending = (float) (clone $product)->where('status', 'pending')->sum('amount');

        return view('dashboards.branch-manager', [
            'manager' => $manager,
            'partnerCount' => $manager->commissionPartners()->count(),
            'stats' => [
                'earned' => round($vipRevenue + $productRevenue, 2),
                'activations' => (clone $base)->count(),
            ],
            'revenue' => [
                'vip' => round($vipRevenue, 2),
                'product' => round($productRevenue, 2),
                'product_pending' => round($productPending, 2),
                'total' => round($vipRevenue + $productRevenue, 2),
            ],
            'revenueChart' => ChartData::monthly(clone $base, 'activated_at', 'SUM(branch_manager_amount)'),
            'partnerChart' => [
                'categories' => $byPartner->map(fn ($row) => $row->commissionPartner->name ?? '—')->all(),
                'data' => $byPartner->map(fn ($row) => round((float) $row->earned, 2))->all(),
            ],
        ]);
    }
}
