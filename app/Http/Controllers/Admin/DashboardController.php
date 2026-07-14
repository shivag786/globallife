<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CommissionTransaction;
use App\Models\Lead;
use App\Models\User;
use App\Models\VipPlan;
use App\Support\ChartData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);

        $stats = $isAdmin
            ? [
                'cities' => City::count(),
                'branch_managers' => User::role('branch_manager')->count(),
                'commission_partners' => User::role('commission_partner')->count(),
                'vip_plans' => VipPlan::where('status', 'active')->count(),
                'vip_members' => User::role('vip_member')->count(),
            ]
            : null;

        return view('dashboards.admin', [
            'user' => $user,
            'stats' => $stats,
            'charts' => $isAdmin ? $this->charts() : null,
            'permissionMatrix' => $user->hasRole('sub_admin')
                ? \App\Services\PermissionMatrixService::groupByModule($user->getAllPermissions()->pluck('name'))
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function charts(): array
    {
        return [
            'revenue' => ChartData::monthly(CommissionTransaction::query(), 'activated_at', 'SUM(company_amount)'),
            'leads' => ChartData::monthly(Lead::query(), 'created_at'),
            'users' => ChartData::monthly(User::query(), 'created_at'),
            'split' => [
                'partners' => (float) CommissionTransaction::sum('commission_partner_amount'),
                'managers' => (float) CommissionTransaction::sum('branch_manager_amount'),
                'company' => (float) CommissionTransaction::sum('company_amount'),
            ],
        ];
    }
}
