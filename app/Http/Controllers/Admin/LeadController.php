<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\User;
use App\Repositories\LeadRepository;
use App\Services\PermissionMatrixService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LeadController extends Controller
{
    public function __construct(private readonly LeadRepository $leads)
    {
    }

    public function index(): View
    {
        $this->ensureModuleAccess();

        return view('admin.leads.index', ['leads' => $this->leads->allPaginated()]);
    }

    public function show(Lead $lead): View
    {
        $this->ensureModuleAccess();

        return view('admin.leads.show', [
            'lead' => $lead->load(['interestedPlan', 'assignedManager']),
            'managers' => User::role('commission_partner')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->leads->update($lead, $request->validated());

        return redirect()->route('admin.leads.show', $lead)->with('status', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        abort_unless(auth()->user()->can('leads.delete'), 403);

        $lead->delete();

        return redirect()->route('admin.leads.index')->with('status', 'Lead deleted.');
    }

    private function ensureModuleAccess(): void
    {
        if (! PermissionMatrixService::userCanAccessModule(auth()->user(), 'leads')) {
            throw new AccessDeniedHttpException;
        }
    }
}
