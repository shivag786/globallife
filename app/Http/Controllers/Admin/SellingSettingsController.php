<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSellingSettingsRequest;
use App\Models\VipPlan;
use App\Services\CompanyProductsSwitch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Per package: do members sell their own products through the company checkout
 * (direct sell), or show them as a catalogue that is ordered on WhatsApp?
 *
 * The two explainer cards at the top are hidden while company products are
 * hidden (CompanyProductsSwitch); the per-package options always stay usable.
 */
class SellingSettingsController extends Controller
{
    public function __construct(private readonly CompanyProductsSwitch $switch) {}

    public function edit(): View
    {
        return view('admin.settings.selling', [
            'plans' => VipPlan::withCount('microsites')->orderBy('display_order')->orderBy('joining_price')->get(),
            'companyProductsHidden' => $this->switch->hidden(),
        ]);
    }

    public function update(UpdateSellingSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated('modes') as $planId => $mode) {
            VipPlan::whereKey($planId)->first()?->update(['selling_mode' => $mode]);
        }

        return back()->with('status', 'Selling options saved. Business pages switch over immediately.');
    }
}
