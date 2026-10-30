<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSellingSettingsRequest;
use App\Models\VipPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Per package: do members sell their own products through the company checkout
 * (direct sell), or show them as a catalogue that is ordered on WhatsApp?
 */
class SellingSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.selling', [
            'plans' => VipPlan::withCount('microsites')->orderBy('display_order')->orderBy('joining_price')->get(),
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
