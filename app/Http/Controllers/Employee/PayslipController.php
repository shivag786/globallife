<?php

namespace App\Http\Controllers\Employee;

use App\Models\Hr\Payslip;
use Illuminate\Contracts\View\View;

/** Payslips of finalized payrolls only; drafts stay with HR. */
class PayslipController extends EmployeePortalController
{
    public function index(): View
    {
        return view('employee.payslips', [
            'payslips' => $this->employee()->payslips()
                ->whereHas('run', fn ($q) => $q->where('status', 'finalized'))
                ->with('run')
                ->get()
                ->sortByDesc(fn (Payslip $p) => $p->run->month)
                ->values(),
        ]);
    }

    public function show(Payslip $payslip): View
    {
        abort_unless($payslip->employee_id === $this->employee()->id && $payslip->run->status === 'finalized', 404);

        return view('hr.payslip', ['payslip' => $payslip->load(['run', 'employee.user', 'employee.department', 'employee.designation'])]);
    }
}
