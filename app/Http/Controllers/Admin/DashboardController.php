<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\User;
use App\Models\VipPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $stats = $user->hasAnyRole(['super_admin', 'admin'])
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
            'permissionMatrix' => $user->hasRole('sub_admin')
                ? \App\Services\PermissionMatrixService::groupByModule($user->getAllPermissions()->pluck('name'))
                : null,
        ]);
    }
}
