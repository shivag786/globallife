<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ChangePasswordRequest;
use App\Services\AccountPasswordService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * "Change Password" for every role. Customers see it in the storefront layout,
 * everyone else inside their dashboard shell.
 */
class PasswordController extends Controller
{
    /** Roles that have a dashboard shell (layouts.app); anyone else gets the storefront. */
    private const DASHBOARD_ROLES = ['super_admin', 'admin', 'sub_admin', 'branch_manager', 'commission_partner', 'vip_member', 'employee'];

    public function __construct(private readonly AccountPasswordService $passwords) {}

    public function edit(): View
    {
        return view('account.password.edit', [
            'layout' => Auth::user()->hasAnyRole(self::DASHBOARD_ROLES) ? 'layouts.app' : 'layouts.public',
        ]);
    }

    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        $this->passwords->setOwnPassword(
            $request->user(),
            $request->validated()['password'],
            $request->session()->getId(),
        );

        // Fresh session id after a credential change; the old row is destroyed.
        $request->session()->regenerate(true);

        return redirect()
            ->route('account.password.edit')
            ->with('status', 'Your password has been changed. Other devices have been signed out.');
    }
}
