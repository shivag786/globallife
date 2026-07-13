<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vip\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    private const FILE_FIELDS = ['logo' => 'logo_path', 'cover_banner' => 'cover_banner_path'];

    public function edit(): View
    {
        $user = Auth::user()->load('vipMicrosite');

        return view('vip.profile.edit', ['user' => $user]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        $microsite = $user->vipMicrosite;

        $user->update(['mobile' => $data['mobile'] ?? null]);

        foreach (self::FILE_FIELDS as $input => $column) {
            unset($data[$input]);

            if ($request->hasFile($input)) {
                $data[$column] = $request->file($input)->store('uploads', 'public');
            }
        }

        $microsite->update($data);

        return redirect()->route('vip.profile.edit')->with('status', 'Profile updated successfully.');
    }
}
