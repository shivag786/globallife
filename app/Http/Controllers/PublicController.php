<?php

namespace App\Http\Controllers;

use App\Repositories\HomeSectionRepository;
use App\Repositories\VipPlanRepository;
use Illuminate\Contracts\View\View;

class PublicController extends Controller
{
    public function home(HomeSectionRepository $sections): View
    {
        return view('welcome', ['sections' => $sections->activeOrdered()]);
    }

    public function vipPlans(VipPlanRepository $plans): View
    {
        return view('vip-plans.index', ['plans' => $plans->activeOrdered()]);
    }
}
