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
use App\Support\BranchManagerAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Branch Managers.
 *
 * Reachable by Super Admin, and by a Sub Admin granted the `branch-managers`
 * module — who sees only the Branch Managers they created. Every entry point
 * runs through BranchManagerAccess so the scoped list is not the only thing
 * standing between a Sub Admin and someone else's account.
 */
class BranchManagerController extends Controller
{
    public function __construct(private readonly BranchManagerService $branchManagers) {}

    public function index(): View
    {
        $actor = $this->authorizeModule();

        $managers = BranchManagerAccess::scope(User::role('branch_manager'), $actor)
            ->with(['branchCities', 'creator'])
            ->withCount('commissionPartners')
            ->orderBy('name')
            ->get();

        return view('admin.branch-managers.index', [
            'managers' => $managers,
            'seesAll' => BranchManagerAccess::seesAll($actor),
        ]);
    }

    public function create(): View
    {
        $this->authorizeModule();

        return view('admin.branch-managers.create', [
            'cities' => City::orderBy('name')->get(),
            'subAdmins' => $this->assignableSubAdmins(),
            'seesAll' => BranchManagerAccess::seesAll(Auth::user()),
        ]);
    }

    public function store(StoreBranchManagerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // A Sub Admin's new Branch Manager belongs to them. Super Admin may name
        // a Sub Admin to hand it to, or keep it themselves.
        $owner = BranchManagerAccess::seesAll(Auth::user())
            ? ($data['created_by'] ?? null ? User::role('sub_admin')->find($data['created_by']) : null)
            : Auth::user();

        $this->branchManagers->createBranchManager(
            $data,
            $data['cities'] ?? [],
            (float) $data['commission_percentage'],
            $owner,
        );

        return redirect()->route('admin.branch-managers.index')->with('status', 'Branch Manager created successfully.');
    }

    public function edit(User $branchManager): View
    {
        $this->authorizeManager($branchManager);

        return view('admin.branch-managers.edit', [
            'manager' => $branchManager->load('branchCities'),
            'cities' => City::orderBy('name')->get(),
            'subAdmins' => $this->assignableSubAdmins(),
            'seesAll' => BranchManagerAccess::seesAll(Auth::user()),
        ]);
    }

    public function update(UpdateBranchManagerRequest $request, User $branchManager): RedirectResponse
    {
        $this->authorizeManager($branchManager);

        $data = $request->validated();

        $this->branchManagers->updateBranchManager($branchManager, $data, $data['cities'] ?? [], (float) $data['commission_percentage']);

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
        // Also confirms it really is a Branch Manager, so the route model being
        // any User never becomes a way to reset an arbitrary password.
        $this->authorizeManager($branchManager);

        $this->branchManagers->setPassword($branchManager, $request->validated()['password']);

        return redirect()
            ->route('admin.branch-managers.edit', $branchManager)
            ->with('status', "Password updated for {$branchManager->name}. They have been signed out everywhere and must use the new password.");
    }

    public function toggleStatus(User $branchManager): RedirectResponse
    {
        $this->authorizeManager($branchManager);

        $this->branchManagers->toggleStatus($branchManager);

        return back()->with('status', "Branch Manager status set to {$branchManager->fresh()->status}.");
    }

    public function permissions(User $branchManager): View
    {
        $this->authorizeManager($branchManager);

        return view('admin.branch-managers.permissions', [
            'manager' => $branchManager,
            'modules' => BranchPermissionMatrixService::activeModules(),
            'actions' => BranchPermissionMatrixService::ACTIONS,
            'granted' => $branchManager->getAllPermissions()->pluck('name')->all(),
        ]);
    }

    public function updatePermissions(Request $request, User $branchManager): RedirectResponse
    {
        $this->authorizeManager($branchManager);

        $submitted = array_keys($request->input('permissions', []));
        $valid = array_intersect($submitted, BranchPermissionMatrixService::allPermissions());

        $branchManager->syncPermissions($valid);

        return redirect()
            ->route('admin.branch-managers.permissions.edit', $branchManager)
            ->with('status', 'Permissions updated successfully.');
    }

    /**
     * 404 rather than 403 when the target is not a Branch Manager at all — it
     * says nothing about whether that account exists — and 403 when it is one
     * this Sub Admin does not own.
     */
    private function authorizeManager(User $manager): void
    {
        $actor = $this->authorizeModule();

        abort_unless($manager->hasRole('branch_manager'), 404);
        abort_unless(BranchManagerAccess::owns($actor, $manager), 403);
    }

    /**
     * The role middleware lets any Sub Admin as far as these routes; holding the
     * `branch-managers` module is what actually opens them. Without this an
     * ungranted Sub Admin would get a working page that simply listed nothing.
     */
    private function authorizeModule(): User
    {
        $actor = Auth::user();

        abort_unless(BranchManagerAccess::canManage($actor), 403);

        return $actor;
    }

    /**
     * Sub Admins a Branch Manager may be handed to. Empty for a Sub Admin, who
     * never sees the owner field in the first place.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function assignableSubAdmins()
    {
        if (! BranchManagerAccess::seesAll(Auth::user())) {
            return User::query()->whereRaw('1 = 0')->get();
        }

        return User::role('sub_admin')->orderBy('name')->get();
    }
}
