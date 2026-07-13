<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function edit(): View
    {
        return view('admin.settings.edit', ['settings' => $this->settings->all()]);
    }

    private const FILE_KEYS = ['site_logo', 'favicon', 'og_image'];

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            if (in_array($key, self::FILE_KEYS, true)) {
                continue;
            }

            $this->settings->set($key, $value);
        }

        foreach (self::FILE_KEYS as $key) {
            if ($request->hasFile($key)) {
                $this->settings->set($key, $request->file($key)->store('uploads', 'public'));
            }
        }

        return redirect()->route('admin.settings.edit')->with('status', 'Settings updated successfully.');
    }
}
