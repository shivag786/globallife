<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CommissionEarning;
use App\Models\CommissionTransaction;
use App\Models\Lead;
use App\Models\Order;
use App\Models\User;
use App\Models\VipPlan;
use App\Models\WithdrawalRequest;
use App\Services\PermissionMatrixService;
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
            'overview' => $isAdmin ? $this->overview() : null,
            'isSuperAdmin' => $user->hasRole('super_admin'),
            'charts' => $isAdmin ? $this->charts() : null,
            // Drives the "Withdrawal Requests" box; Super-Admin-only, like the
            // page it links to.
            'withdrawals' => $user->hasRole('super_admin')
                ? [
                    'pending' => WithdrawalRequest::pending()->count(),
                    'pending_amount' => (float) WithdrawalRequest::pending()->sum('amount'),
                ]
                : null,
            'permissionMatrix' => $user->hasRole('sub_admin')
                ? PermissionMatrixService::groupByModule($user->getAllPermissions()->pluck('name'))
                : null,
        ]);
    }

    /**
     * The at-a-glance sales & revenue picture for the top of the dashboard.
     *
     * Company income has two streams: the remainder the company keeps on each
     * product sale (line total minus everyone's paid commission) and the
     * `company_amount` share of every VIP-plan activation. Product revenue is
     * only counted once an order is delivered — that's when its commissions are
     * approved, so the remainder is realised.
     *
     * @return array<string, mixed>
     */
    private function overview(): array
    {
        $today = today();

        $deliveredSubtotal = (float) Order::where('status', 'delivered')->sum('subtotal');
        $paidToUpline = (float) CommissionEarning::where('status', 'approved')->sum('amount');
        $productRevenue = round(max(0, $deliveredSubtotal - $paidToUpline), 2);

        $vipPlanRevenue = round((float) CommissionTransaction::sum('company_amount'), 2);

        return [
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'revenue_today' => round((float) Order::whereDate('created_at', $today)->sum('total'), 2),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'pending_commission' => round((float) CommissionEarning::where('status', 'pending')->sum('amount'), 2),
            'company_commission' => round($vipPlanRevenue + $productRevenue, 2),
            'vip' => [
                'count' => CommissionTransaction::count(),
                'revenue' => $vipPlanRevenue,
            ],
            'products' => [
                'count' => Order::where('status', 'delivered')->count(),
                'revenue' => $productRevenue,
            ],
        ];
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
