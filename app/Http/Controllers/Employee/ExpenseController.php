<?php

namespace App\Http\Controllers\Employee;

use App\Models\Hr\ExpenseClaim;
use App\Services\Hr\ExpenseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends EmployeePortalController
{
    public function index(): View
    {
        $employee = $this->employee();

        return view('employee.expenses', [
            'claims' => $employee->expenseClaims()->with('reviewer')->latest()->paginate(15),
            'categories' => ExpenseClaim::CATEGORIES,
            'totals' => [
                'pending' => $employee->expenseClaims()->where('status', 'pending')->sum('amount'),
                'approved' => $employee->expenseClaims()->where('status', 'approved')->sum('amount'),
                'paid' => $employee->expenseClaims()->where('status', 'paid')->sum('amount'),
            ],
        ]);
    }

    public function store(Request $request, ExpenseService $expenses): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(ExpenseClaim::CATEGORIES)],
            'amount' => ['required', 'numeric', 'min:1', 'max:9999999'],
            'expense_date' => ['required', 'date', 'before_or_equal:'.\App\Support\HrClock::today()->toDateString()],
            'description' => ['nullable', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $expenses->submit($this->employee(), $data, $request->file('receipt'));

        return back()->with('status', 'Expense claim submitted to HR.');
    }
}
