<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect an authenticated user to their role's dashboard.
     */
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        return match (true) {
            $user->hasAnyRole(['super_admin', 'admin', 'sub_admin']) => redirect()->route('admin.dashboard'),
            $user->hasRole('branch_manager') => redirect()->route('branch.dashboard'),
            $user->hasRole('commission_partner') => redirect()->route('manager.dashboard'),
            $user->hasRole('vip_member') => redirect()->route('vip.dashboard'),
            $user->hasRole('customer') => redirect()->route('account.orders.index'),
            default => redirect()->route('home'),
        };
    }
}
