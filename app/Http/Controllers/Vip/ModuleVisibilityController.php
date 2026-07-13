<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Support\BusinessModules;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleVisibilityController extends Controller
{
    public function edit(): View
    {
        $microsite = Auth::user()->vipMicrosite;
        $current = array_merge(BusinessModules::defaults(), $microsite->module_visibility ?? []);

        return view('vip.modules.edit', [
            'sections' => BusinessModules::SECTIONS,
            'floatingButtons' => BusinessModules::FLOATING_BUTTONS,
            'current' => $current,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $microsite = Auth::user()->vipMicrosite;
        $allowedKeys = array_keys(BusinessModules::defaults());
        $submitted = array_keys($request->input('modules', []));

        $map = BusinessModules::defaults();
        foreach ($allowedKeys as $key) {
            $map[$key] = in_array($key, $submitted, true);
        }

        $microsite->update(['module_visibility' => $map]);

        return redirect()->route('vip.modules.edit')->with('status', 'Section visibility updated.');
    }
}
