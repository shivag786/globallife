<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreCommissionPartnerRequest;
use App\Http\Requests\Branch\UpdateCommissionPartnerRequest;
use App\Models\User;
use App\Services\CommissionPartnerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CommissionPartnerController extends Controller
{
    public function __construct(private readonly CommissionPartnerService $partners)
    {
    }

    public function index(): View
    {
        $partners = Auth::user()->commissionPartners()->with('cities')->orderBy('name')->get();

        return view('branch.commission-partners.index', ['partners' => $partners]);
    }

    public function create(): View
    {
        return view('branch.commission-partners.create', ['cities' => Auth::user()->branchCities]);
    }

    public function store(StoreCommissionPartnerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->partners->createPartner($data, $data['cities'], (float) $data['commission_percentage'], Auth::user());

        return redirect()->route('branch.commission-partners.index')->with('status', 'Commission Partner created successfully.');
    }

    public function edit(User $commissionPartner): View
    {
        abort_unless($commissionPartner->created_by === Auth::id(), 403);

        return view('branch.commission-partners.edit', [
            'partner' => $commissionPartner->load('cities'),
            'cities' => Auth::user()->branchCities,
        ]);
    }

    public function update(UpdateCommissionPartnerRequest $request, User $commissionPartner): RedirectResponse
    {
        $data = $request->validated();

        $this->partners->updatePartner($commissionPartner, $data, $data['cities'], (float) $data['commission_percentage']);

        return redirect()->route('branch.commission-partners.index')->with('status', 'Commission Partner updated successfully.');
    }

    public function toggleStatus(User $commissionPartner): RedirectResponse
    {
        abort_unless($commissionPartner->created_by === Auth::id(), 403);

        $this->partners->toggleStatus($commissionPartner);

        return back()->with('status', "Commission Partner status set to {$commissionPartner->fresh()->status}.");
    }
}
