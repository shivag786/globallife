<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
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

        return view('dashboards.commission-partner', [
            'manager' => $manager,
            'stats' => [
                'earned' => (float) (clone $base)->sum('commission_partner_amount'),
                'activations' => (clone $base)->count(),
                'vip_members' => $manager->vipMembers()->count(),
            ],
            'earningsChart' => ChartData::monthly(clone $base, 'activated_at', 'SUM(commission_partner_amount)'),
        ]);
    }
}
