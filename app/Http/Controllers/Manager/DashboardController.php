<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $manager = Auth::user()->load('cities', 'creator');

        return view('dashboards.commission-partner', ['manager' => $manager]);
    }
}
