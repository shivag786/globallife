<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreCommissionPartnerRequest;
use App\Http\Requests\Branch\UpdateCommissionPartnerRequest;
use App\Models\User;
use App\Services\CityDirectoryService;
use App\Services\CommissionPartnerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CommissionPartnerController extends Controller
{
    public function __construct(
        private readonly CommissionPartnerService $partners,
        private readonly CityDirectoryService $cities,
    ) {}

    /**
     * Everything the cascading state -> city picker needs.
     *
     * @return array<string, mixed>
     */
    private function pickerData(): array
    {
        return [
            'states' => $this->cities->states(),
            'citiesByState' => $this->cities->citiesByState(),
        ];
    }

    public function index(): View
    {
        $partners = Auth::user()->commissionPartners()->with('cities')->orderBy('name')->get();

        return view('branch.commission-partners.index', ['partners' => $partners]);
    }

    public function create(): View
    {
        return view('branch.commission-partners.create', $this->pickerData());
    }

    public function store(StoreCommissionPartnerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $cityIds = $this->partners->resolveCities($data, Auth::user());

        $this->partners->createPartner($data, $cityIds, (float) $data['commission_percentage'], Auth::user());

        return redirect()->route('branch.commission-partners.index')->with('status', 'Commission Partner created successfully.');
    }

    public function edit(User $commissionPartner): View
    {
        abort_unless($commissionPartner->created_by === Auth::id(), 403);

        return view('branch.commission-partners.edit', $this->pickerData() + [
            'partner' => $commissionPartner->load('cities'),
        ]);
    }

    public function update(UpdateCommissionPartnerRequest $request, User $commissionPartner): RedirectResponse
    {
        $data = $request->validated();
        $cityIds = $this->partners->resolveCities($data, Auth::user());

        $this->partners->updatePartner($commissionPartner, $data, $cityIds, (float) $data['commission_percentage']);

        return redirect()->route('branch.commission-partners.index')->with('status', 'Commission Partner updated successfully.');
    }

    public function toggleStatus(User $commissionPartner): RedirectResponse
    {
        abort_unless($commissionPartner->created_by === Auth::id(), 403);

        $this->partners->toggleStatus($commissionPartner);

        return back()->with('status', "Commission Partner status set to {$commissionPartner->fresh()->status}.");
    }
}
