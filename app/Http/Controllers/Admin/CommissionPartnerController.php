<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCommissionPartnerRequest;
use App\Models\User;
use App\Repositories\CommissionPartnerUpdateRepository;
use App\Services\CommissionPartnerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Super Admin's view of every Commission Partner across all Branch Managers.
 *
 * Partners are created and normally managed by their Branch Manager. Super Admin
 * can also edit their details; each such edit leaves a note for that Branch
 * Manager (CommissionPartnerUpdate) so the change is never silent.
 */
class CommissionPartnerController extends Controller
{
    public function __construct(private readonly CommissionPartnerService $partners) {}

    public function index(): View
    {
        $partners = User::role('commission_partner')
            ->with(['cities', 'creator'])
            ->orderBy('name')
            ->get();

        return view('admin.commission-partners.index', ['partners' => $partners]);
    }

    public function edit(User $commissionPartner, CommissionPartnerUpdateRepository $updates): View
    {
        abort_unless($commissionPartner->hasRole('commission_partner'), 404);

        return view('admin.commission-partners.edit', [
            'partner' => $commissionPartner->load('creator', 'profile'),
            'cap' => (float) ($commissionPartner->creator?->commission_percentage ?? 100),
            'history' => $updates->historyFor($commissionPartner),
        ]);
    }

    public function update(UpdateCommissionPartnerRequest $request, User $commissionPartner): RedirectResponse
    {
        abort_unless($commissionPartner->hasRole('commission_partner'), 404);

        $note = $this->partners->adminUpdatePartner($commissionPartner, $request->validated(), Auth::user());

        $branchManager = $commissionPartner->creator?->name;

        $status = match (true) {
            $note === null => 'Nothing changed.',
            $branchManager !== null => "{$commissionPartner->name} updated. {$branchManager} has been notified on their dashboard.",
            default => "{$commissionPartner->name} updated.",
        };

        return redirect()->route('admin.commission-partners.edit', $commissionPartner)->with('status', $status);
    }
}
