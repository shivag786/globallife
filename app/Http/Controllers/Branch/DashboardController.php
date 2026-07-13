<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Services\BranchPermissionMatrixService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $manager = Auth::user()->load('branchCities');

        return view('dashboards.branch-manager', [
            'manager' => $manager,
            'partnerCount' => $manager->commissionPartners()->count(),
            'modules' => BranchPermissionMatrixService::MODULES,
        ]);
    }
}
