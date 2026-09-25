<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreCommissionPartnerRequest;
use App\Http\Requests\Branch\UpdateCommissionPartnerPasswordRequest;
use App\Http\Requests\Branch\UpdateCommissionPartnerRequest;
use App\Models\User;
use App\Services\CommissionPartnerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CommissionPartnerController extends Controller
{
    public function __construct(private readonly CommissionPartnerService $partners) {}

    public function index(): View
    {
        $partners = Auth::user()->commissionPartners()->with('cities')->orderBy('name')->get();

        return view('branch.commission-partners.index', ['partners' => $partners]);
    }

    public function create(): View
    {
        return view('branch.commission-partners.create');
    }

    public function store(StoreCommissionPartnerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->partners->createPartner($data, (float) $data['commission_percentage'], Auth::user());

        return redirect()->route('branch.commission-partners.index')->with('status', 'Commission Partner created successfully.');
    }

    public function edit(User $commissionPartner): View
    {
        abort_unless($commissionPartner->created_by === Auth::id(), 403);

        return view('branch.commission-partners.edit', [
            'partner' => $commissionPartner,
        ]);
    }

    public function update(UpdateCommissionPartnerRequest $request, User $commissionPartner): RedirectResponse
    {
        $data = $request->validated();

        $this->partners->updatePartner($commissionPartner, $data, (float) $data['commission_percentage']);

        return redirect()->route('branch.commission-partners.index')->with('status', 'Commission Partner updated successfully.');
    }

    /**
     * Set a Commission Partner's password. Its own endpoint and its own form on
     * the edit screen, so it stays a deliberate act rather than riding along
     * with a profile save. Branch Manager side only.
     */
    public function updatePassword(UpdateCommissionPartnerPasswordRequest $request, User $commissionPartner): RedirectResponse
    {
        // The route model is any User, so confirm it really is one of their
        // Commission Partners before this becomes a way to reset anyone.
        abort_unless($commissionPartner->hasRole('commission_partner'), 404);

        $this->partners->setPassword($commissionPartner, $request->validated()['password']);

        return redirect()
            ->route('branch.commission-partners.edit', $commissionPartner)
            ->with('status', "Password updated for {$commissionPartner->name}. They have been signed out everywhere and must use the new password.");
    }

    public function toggleStatus(User $commissionPartner): RedirectResponse
    {
        abort_unless($commissionPartner->created_by === Auth::id(), 403);

        $this->partners->toggleStatus($commissionPartner);

        return back()->with('status', "Commission Partner status set to {$commissionPartner->fresh()->status}.");
    }
}
