<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVipPlanRequest;
use App\Http\Requests\Admin\UpdateVipPlanRequest;
use App\Models\VipPlan;
use App\Repositories\VipPlanRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VipPlanController extends Controller
{
    public function __construct(private readonly VipPlanRepository $plans)
    {
    }

    public function index(): View
    {
        return view('admin.vip-plans.index', ['plans' => $this->plans->allOrdered()]);
    }

    public function create(): View
    {
        return view('admin.vip-plans.create');
    }

    public function store(StoreVipPlanRequest $request): RedirectResponse
    {
        $this->plans->create($request->validated());

        return redirect()->route('admin.vip-plans.index')->with('status', 'VIP Plan created successfully.');
    }

    public function edit(VipPlan $vipPlan): View
    {
        return view('admin.vip-plans.edit', ['plan' => $vipPlan]);
    }

    public function update(UpdateVipPlanRequest $request, VipPlan $vipPlan): RedirectResponse
    {
        $this->plans->update($vipPlan, $request->validated());

        return redirect()->route('admin.vip-plans.index')->with('status', 'VIP Plan updated successfully.');
    }

    public function destroy(VipPlan $vipPlan): RedirectResponse
    {
        $vipPlan->delete();

        return redirect()->route('admin.vip-plans.index')->with('status', 'VIP Plan deleted.');
    }
}
