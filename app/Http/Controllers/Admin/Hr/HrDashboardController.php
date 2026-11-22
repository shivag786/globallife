<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Repositories\HrDashboardRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class HrDashboardController extends Controller
{
    public function index(HrDashboardRepository $hr): View
    {
        abort_unless(\App\Support\HrAccess::any(Auth::user()), 403);

        return view('admin.hr.dashboard', [
            'today' => $hr->today(),
            'pending' => $hr->pending(),
            'departments' => $hr->departmentHeadcount(),
            'celebrations' => $hr->upcomingCelebrations(),
            'recentJoiners' => $hr->recentJoiners(),
            'latestPayroll' => $hr->latestPayroll(),
        ]);
    }
}
