<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBranchManagerRequest;
use App\Http\Requests\Admin\UpdateBranchManagerPasswordRequest;
use App\Http\Requests\Admin\UpdateBranchManagerRequest;
use App\Models\City;
use App\Models\User;
use App\Services\BranchManagerService;
use App\Services\BranchPermissionMatrixService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchManagerController extends Controller
{
    public function __construct(private readonly BranchManagerService $branchManagers) {}

    public function index(): View
    {
        $managers = User::role('branch_manager')
            ->with('branchCities')
            ->withCount('commissionPartners')
            ->orderBy('name')
            ->get();

        return view('admin.branch-managers.index', ['managers' => $managers]);
    }

    public function create(): View
    {
        return view('admin.branch-managers.create', ['cities' => City::orderBy('name')->get()]);
    }

    public function store(StoreBranchManagerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->branchManagers->createBranchManager($data, $data['cities'], (float) $data['commission_percentage']);

        return redirect()->route('admin.branch-managers.index')->with('status', 'Branch Manager created successfully.');
    }

    public function edit(User $branchManager): View
    {
        return view('admin.branch-managers.edit', [
            'manager' => $branchManager->load('branchCities'),
            'cities' => City::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateBranchManagerRequest $request, User $branchManager): RedirectResponse
    {
        $data = $request->validated();

        $this->branchManagers->updateBranchManager($branchManager, $data, $data['cities'], (float) $data['commission_percentage']);

        return redirect()->route('admin.branch-managers.index')->with('status', 'Branch Manager updated successfully.');
    }

    /**
     * Set a Branch Manager's password. Deliberately its own endpoint and its own
     * form on the edit screen: it is the only place in the app where one account
     * may change another's, so it stays explicit rather than riding along with a
     * profile save.
     */
    public function updatePassword(UpdateBranchManagerPasswordRequest $request, User $branchManager): RedirectResponse
    {
        // The route model is any User, so confirm it really is a Branch Manager
        // before this becomes a way to reset anyone's password.
        abort_unless($branchManager->hasRole('branch_manager'), 404);

        $this->branchManagers->setPassword($branchManager, $request->validated()['password']);

        return redirect()
            ->route('admin.branch-managers.edit', $branchManager)
            ->with('status', "Password updated for {$branchManager->name}. They have been signed out everywhere and must use the new password.");
    }

    public function toggleStatus(User $branchManager): RedirectResponse
    {
        $this->branchManagers->toggleStatus($branchManager);

        return back()->with('status', "Branch Manager status set to {$branchManager->fresh()->status}.");
    }

    public function permissions(User $branchManager): View
    {
        return view('admin.branch-managers.permissions', [
            'manager' => $branchManager,
            'modules' => BranchPermissionMatrixService::MODULES,
            'actions' => BranchPermissionMatrixService::ACTIONS,
            'granted' => $branchManager->getAllPermissions()->pluck('name')->all(),
        ]);
    }

    public function updatePermissions(Request $request, User $branchManager): RedirectResponse
    {
        $submitted = array_keys($request->input('permissions', []));
        $valid = array_intersect($submitted, BranchPermissionMatrixService::allPermissions());

        $branchManager->syncPermissions($valid);

        return redirect()
            ->route('admin.branch-managers.permissions.edit', $branchManager)
            ->with('status', 'Permissions updated successfully.');
    }
}
