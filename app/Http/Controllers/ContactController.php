<?php

namespace App\Http\Controllers;

use App\Repositories\VipPlanRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show(Request $request, VipPlanRepository $plans): View
    {
        return view('contact', [
            'plans' => $plans->activeOrdered(),
            'preselectedPlan' => $request->query('plan'),
        ]);
    }
}
