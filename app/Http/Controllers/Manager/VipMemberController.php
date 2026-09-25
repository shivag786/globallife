<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreVipMemberRequest;
use App\Http\Requests\Manager\UpdateVipMemberRequest;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Repositories\VipPlanRepository;
use App\Services\CityDirectoryService;
use App\Services\VipActivationService;
use App\Services\VipMemberService;
use App\Services\VipRenewalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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

    public function create(VipPlanRepository $plans, CityDirectoryService $cities): View
    {
        return view('manager.vip-members.create', [
            'states' => $cities->states(),
            'citiesByState' => $cities->citiesByState(),
            'plans' => $plans->activeOrdered(),
        ]);
    }

    /**
     * Adding a member activates their plan straight away — the page goes live and
     * the commission split is booked. There is no separate Activate step for new
     * members; see VipMemberService::createMember().
     */
    public function store(StoreVipMemberRequest $request, CityDirectoryService $cities): RedirectResponse
    {
        try {
            $member = $this->members->createMember(
                $request->validated(),
                Auth::user(),
                $request->resolveCity($cities),
            );
        } catch (RuntimeException $e) {
            // The split could not be recorded, so nothing was created. Say why
            // rather than dropping them on a list with no new member on it.
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('manager.vip-members.index')->with('status', sprintf(
            'VIP Member created and activated — %s is live until %s, and the commission is recorded.',
            $member->name,
            $member->vipMicrosite->plan_expires_at->format('d M Y'),
        ));
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

    /**
     * Activate a member by hand.
     *
     * New members are activated as they are created, so this only ever answers
     * accounts that predate that — rows still sitting at activated_at NULL. It
     * stays because those pages would otherwise have no way to go live.
     */
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
     * The renewal screen: the packages on offer, what the member currently uses,
     * and Approve / Reject. Open from 30 days before expiry onwards, so a page
     * never has to go dark while payment is being collected.
     */
    public function renewal(User $vipMember, VipPlanRepository $plans): View|RedirectResponse
    {
        abort_unless($vipMember->created_by === Auth::id(), 403);

        $microsite = $vipMember->vipMicrosite;

        if (! $microsite?->isRenewalDue()) {
            return redirect()->route('manager.vip-members.index')->with('error', sprintf(
                'That member is not due for renewal yet — renewals open %d days before expiry.',
                VipMicrosite::RENEWAL_WINDOW_DAYS,
            ));
        }

        return view('manager.vip-members.renewal', [
            'member' => $vipMember,
            'microsite' => $microsite->load('vipPlan'),
            'packages' => $plans->activeOrdered(),
            'productQuota' => $microsite->contentQuota('products'),
            'serviceQuota' => $microsite->contentQuota('services'),
            'history' => $microsite->renewals()->with('vipPlan', 'decider')->latest('decided_at')->get(),
        ]);
    }

    /**
     * Approving renews onto the chosen package: it sets the new validity window
     * and the member's product/service caps, and brings the microsite back.
     */
    public function approveRenewal(Request $request, User $vipMember): RedirectResponse
    {
        abort_unless($vipMember->created_by === Auth::id(), 403);

        $data = $request->validate([
            'vip_plan_id' => ['required', 'integer', Rule::exists('vip_plans', 'id')->where('status', 'active')],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        return $this->decideRenewal(
            $vipMember,
            'approve',
            VipPlan::findOrFail($data['vip_plan_id']),
            $data['note'] ?? null,
        );
    }

    public function rejectRenewal(Request $request, User $vipMember): RedirectResponse
    {
        abort_unless($vipMember->created_by === Auth::id(), 403);

        return $this->decideRenewal($vipMember, 'reject', null, $request->input('note'));
    }

    private function decideRenewal(User $vipMember, string $decision, ?VipPlan $plan, ?string $note): RedirectResponse
    {
        $microsite = $vipMember->vipMicrosite;

        if (! $microsite) {
            return back()->with('error', 'This VIP Member has no microsite to renew.');
        }

        try {
            $renewal = $decision === 'approve'
                ? $this->renewals->approve($microsite, Auth::user(), $plan, $note)
                : $this->renewals->reject($microsite, Auth::user(), $note);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! $renewal->isApproved()) {
            return redirect()->route('manager.vip-members.index')
                ->with('status', "Renewal rejected — {$vipMember->name}'s page stays on the maintenance notice.");
        }

        return redirect()->route('manager.vip-members.index')->with('status', sprintf(
            'Renewal approved on %s — %s is live until %s, with %d products and %d services allowed.',
            $plan->name,
            $vipMember->name,
            $renewal->new_expires_at->format('d M Y'),
            $plan->productLimit(),
            $plan->serviceLimit(),
        ));
    }
}
