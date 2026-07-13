<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Repositories\LeadRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function index(LeadRepository $leads): View
    {
        return view('manager.leads.index', ['leads' => $leads->forManager(Auth::user())]);
    }
}
