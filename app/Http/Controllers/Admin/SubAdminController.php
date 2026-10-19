<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubAdminRequest;
use App\Http\Requests\Admin\UpdateSubAdminPasswordRequest;
use App\Http\Requests\Admin\UpdateSubAdminRequest;
use App\Models\User;
use App\Services\MemberProfileService;
use App\Services\PermissionMatrixService;
use App\Services\SubAdminService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Sub Admins — the layer between Super Admin and Branch Manager.
 *
 * Super Admin only. What a Sub Admin can actually reach is the permission matrix
 * (`PermissionMatrixService`), granted per module on the permissions screen; the
 * `branch-managers` module is the one that puts them into the user hierarchy.
 */
class SubAdminController extends Controller
{
    public function __construct(private readonly SubAdminService $subAdmins) {}

    public function index(): View
    {
        $subAdmins = User::role('sub_admin')
            ->withCount('branchManagers')
            ->orderBy('name')
            ->get();

        return view('admin.sub-admins.index', ['subAdmins' => $subAdmins]);
    }

    public function create(): View
    {
        return view('admin.sub-admins.create');
    }

    public function store(StoreSubAdminRequest $request, MemberProfileService $profiles): RedirectResponse
    {
        // The account and its personal / KYC details land together or not at all.
        $subAdmin = DB::transaction(function () use ($request, $profiles) {
            $subAdmin = $this->subAdmins->createSubAdmin($request->validated(), Auth::user());
            $profiles->save($subAdmin, $request->profileData(), $request->profileFiles(), $request->profileRemovals());

            return $subAdmin;
        });

        return redirect()
            ->route('admin.sub-admins.permissions.edit', $subAdmin)
            ->with('status', "Sub Admin created. Choose what {$subAdmin->name} can access below — a new Sub Admin starts with nothing granted.");
    }

    public function edit(User $subAdmin): View
    {
        abort_unless($subAdmin->hasRole('sub_admin'), 404);

        return view('admin.sub-admins.edit', ['subAdmin' => $subAdmin->load('profile')]);
    }

    public function update(UpdateSubAdminRequest $request, User $subAdmin, MemberProfileService $profiles): RedirectResponse
    {
        abort_unless($subAdmin->hasRole('sub_admin'), 404);

        DB::transaction(function () use ($request, $subAdmin, $profiles) {
            $this->subAdmins->updateSubAdmin($subAdmin, $request->validated());
            $profiles->save($subAdmin, $request->profileData(), $request->profileFiles(), $request->profileRemovals());
        });

        return redirect()->route('admin.sub-admins.index')->with('status', 'Sub Admin updated successfully.');
    }

    /**
     * Set a Sub Admin's password. Its own endpoint and its own form on the edit
     * screen, for the same reason as the Branch Manager reset: changing someone
     * else's credentials stays explicit rather than riding along with a save.
     */
    public function updatePassword(UpdateSubAdminPasswordRequest $request, User $subAdmin): RedirectResponse
    {
        abort_unless($subAdmin->hasRole('sub_admin'), 404);

        $this->subAdmins->setPassword($subAdmin, $request->validated()['password']);

        return redirect()
            ->route('admin.sub-admins.edit', $subAdmin)
            ->with('status', "Password updated for {$subAdmin->name}. They have been signed out everywhere and must use the new password.");
    }

    public function toggleStatus(User $subAdmin): RedirectResponse
    {
        abort_unless($subAdmin->hasRole('sub_admin'), 404);

        $this->subAdmins->toggleStatus($subAdmin);

        return back()->with('status', "Sub Admin status set to {$subAdmin->fresh()->status}.");
    }

    public function permissions(User $subAdmin): View
    {
        abort_unless($subAdmin->hasRole('sub_admin'), 404);

        return view('admin.sub-admins.permissions', [
            'subAdmin' => $subAdmin,
            'modules' => PermissionMatrixService::MODULES,
            'granted' => $subAdmin->getAllPermissions()->pluck('name')->all(),
        ]);
    }

    public function updatePermissions(Request $request, User $subAdmin): RedirectResponse
    {
        abort_unless($subAdmin->hasRole('sub_admin'), 404);

        // Intersected against the matrix so a hand-rolled request cannot grant a
        // permission that is not on offer.
        $submitted = array_keys($request->input('permissions', []));
        $valid = array_intersect($submitted, PermissionMatrixService::allPermissions());

        $subAdmin->syncPermissions($valid);

        return redirect()
            ->route('admin.sub-admins.permissions.edit', $subAdmin)
            ->with('status', 'Permissions updated successfully.');
    }
}
