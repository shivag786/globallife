<?php

namespace App\Http\Controllers\Manager;

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
        $manager = Auth::user()->load('cities', 'creator');
        $base = CommissionTransaction::where('commission_partner_id', $manager->id);

        // Two income streams: VIP-plan activations and product-sale commission.
        $vipRevenue = (float) (clone $base)->sum('commission_partner_amount');
        $product = CommissionEarning::where('beneficiary_id', $manager->id);
        $productRevenue = (float) (clone $product)->where('status', 'approved')->sum('amount');
        $productPending = (float) (clone $product)->where('status', 'pending')->sum('amount');

        return view('dashboards.commission-partner', [
            'manager' => $manager,
            'stats' => [
                'earned' => round($vipRevenue + $productRevenue, 2),
                'activations' => (clone $base)->count(),
                'vip_members' => $manager->vipMembers()->count(),
            ],
            'revenue' => [
                'vip' => round($vipRevenue, 2),
                'product' => round($productRevenue, 2),
                'product_pending' => round($productPending, 2),
                'total' => round($vipRevenue + $productRevenue, 2),
            ],
            'earningsChart' => ChartData::monthly(clone $base, 'activated_at', 'SUM(commission_partner_amount)'),
        ]);
    }
}
