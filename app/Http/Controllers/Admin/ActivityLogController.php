<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $activities = Activity::with('causer')
            ->when($request->filled('causer_id'), fn ($query) => $query->where('causer_id', $request->integer('causer_id')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $causers = User::role(['branch_manager', 'commission_partner'])->orderBy('name')->get();

        return view('admin.activity-logs.index', [
            'activities' => $activities,
            'causers' => $causers,
            'selectedCauserId' => $request->integer('causer_id') ?: null,
        ]);
    }
}
