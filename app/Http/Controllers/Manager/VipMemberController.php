<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreVipMemberRequest;
use App\Http\Requests\Manager\UpdateVipMemberRequest;
use App\Models\User;
use App\Repositories\VipPlanRepository;
use App\Services\VipActivationService;
use App\Services\VipMemberService;
use App\Services\VipRenewalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class VipMemberController extends Controller
{
    public function __construct(
        private readonly VipMemberService $members,
        private readonly VipActivationService $activations,
        private readonly VipRenewalService $renewals,
    ) {}

    public function index(): View
    {
        $members = Auth::user()->vipMembers()
            ->with([
                'vipMicrosite.city',
                'vipMicrosite.vipPlan',
                // Latest renewal decision only — drives the "Last decision" hint.
                'vipMicrosite.renewals' => fn ($q) => $q->latest('decided_at')->limit(1),
            ])
            ->orderBy('name')
            ->get();

        return view('manager.vip-members.index', ['members' => $members]);
    }

    public function create(VipPlanRepository $plans): View
    {
        return view('manager.vip-members.create', [
            'cities' => Auth::user()->cities,
            'plans' => $plans->activeOrdered(),
        ]);
    }

    public function store(StoreVipMemberRequest $request): RedirectResponse
    {
        $this->members->createMember($request->validated(), Auth::user());

        return redirect()->route('manager.vip-members.index')->with('status', 'VIP Member created successfully.');
    }

    public function edit(User $vipMember, VipPlanRepository $plans): View
    {
        abort_unless($vipMember->created_by === Auth::id(), 403);

        return view('manager.vip-members.edit', [
            'member' => $vipMember->load('vipMicrosite.city'),
            'plans' => $plans->activeOrdered(),
        ]);
    }

    public function update(UpdateVipMemberRequest $request, User $vipMember): RedirectResponse
    {
        $this->members->updateMember($vipMember, $request->validated());

        return redirect()->route('manager.vip-members.index')->with('status', 'VIP Member updated successfully.');
    }

    public function toggleStatus(User $vipMember): RedirectResponse
    {
        abort_unless($vipMember->created_by === Auth::id(), 403);

        $this->members->toggleStatus($vipMember);

        return back()->with('status', "VIP Member status set to {$vipMember->fresh()->status}.");
    }

    public function activate(User $vipMember): RedirectResponse
    {
        abort_unless($vipMember->created_by === Auth::id(), 403);

        try {
            $this->activations->activate($vipMember->vipMicrosite, Auth::user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'VIP Member activated — commission recorded.');
    }

    /**
     * Renewal decisions on an EXPIRED plan. Approving restarts the paid cycle and
     * brings the public microsite back; rejecting records the refusal and leaves
     * it on the maintenance page.
     */
    public function approveRenewal(User $vipMember): RedirectResponse
    {
        return $this->decideRenewal($vipMember, 'approve');
    }

    public function rejectRenewal(User $vipMember): RedirectResponse
    {
        return $this->decideRenewal($vipMember, 'reject');
    }

    private function decideRenewal(User $vipMember, string $decision): RedirectResponse
    {
        abort_unless($vipMember->created_by === Auth::id(), 403);

        $microsite = $vipMember->vipMicrosite;

        if (! $microsite) {
            return back()->with('error', 'This VIP Member has no microsite to renew.');
        }

        try {
            $renewal = $decision === 'approve'
                ? $this->renewals->approve($microsite, Auth::user())
                : $this->renewals->reject($microsite, Auth::user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $renewal->isApproved()
            ? "Renewal approved — {$vipMember->name}'s page is live again until {$renewal->new_expires_at->format('d M Y')}."
            : "Renewal rejected — {$vipMember->name}'s page stays on the maintenance notice.");
    }
}
