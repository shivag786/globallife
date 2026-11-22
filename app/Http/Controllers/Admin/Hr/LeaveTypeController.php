<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\LeaveType;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Leave types and their yearly quota. A changed quota applies to balances
 * created from now on (each employee's balance for a year is created on
 * first use), so existing balances for the current year are not rewritten.
 */
class LeaveTypeController extends Controller
{
    public function index(): View
    {
        HrAccess::ability('employees.edit');

        return view('admin.hr.leave-types.index', [
            'types' => LeaveType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        LeaveType::create($this->validated($request));

        return back()->with('status', 'Leave type added.');
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $leaveType->update($this->validated($request, $leaveType));

        return back()->with('status', 'Leave type updated.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?LeaveType $type = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:10', 'alpha_dash', Rule::unique('leave_types', 'code')->ignore($type?->id)],
            'days_per_year' => ['required', 'numeric', 'min:0', 'max:365'],
            'max_carry_forward' => ['nullable', 'numeric', 'min:0', 'max:365'],
        ]);

        return [
            ...$data,
            'code' => strtoupper($data['code']),
            'max_carry_forward' => $data['max_carry_forward'] ?? 0,
            'is_paid' => $request->boolean('is_paid'),
            'carry_forward' => $request->boolean('carry_forward'),
            'is_active' => $type ? $request->boolean('is_active') : true,
        ];
    }
}
