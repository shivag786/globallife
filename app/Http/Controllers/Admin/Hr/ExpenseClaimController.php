<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\ExpenseClaim;
use App\Services\Hr\ExpenseService;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExpenseClaimController extends Controller
{
    public function __construct(private readonly ExpenseService $expenses) {}

    public function index(Request $request): View
    {
        HrAccess::module('expenses');

        $status = $request->input('status', 'pending');

        return view('admin.hr.expenses.index', [
            'status' => $status,
            'claims' => ExpenseClaim::with(['employee.user', 'reviewer'])
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->latest()
                ->paginate(25)
                ->withQueryString(),
            'totals' => [
                'pending' => ExpenseClaim::where('status', 'pending')->sum('amount'),
                'approved' => ExpenseClaim::where('status', 'approved')->sum('amount'),
            ],
        ]);
    }

    public function approve(Request $request, ExpenseClaim $claim): RedirectResponse
    {
        $actor = HrAccess::ability('expenses.approve');

        $request->validate(['review_note' => ['nullable', 'string', 'max:255']]);
        $this->expenses->approve($claim, $actor, $request->input('review_note'));

        return back()->with('status', 'Claim approved. Mark it paid once reimbursed.');
    }

    public function reject(Request $request, ExpenseClaim $claim): RedirectResponse
    {
        $actor = HrAccess::ability('expenses.approve');

        $request->validate(['review_note' => ['nullable', 'string', 'max:255']]);
        $this->expenses->reject($claim, $actor, $request->input('review_note'));

        return back()->with('status', 'Claim rejected.');
    }

    public function markPaid(ExpenseClaim $claim): RedirectResponse
    {
        HrAccess::ability('expenses.approve');

        $this->expenses->markPaid($claim);

        return back()->with('status', 'Claim marked as paid.');
    }
}
