<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use App\Models\Hr\PayrollRun;
use App\Models\Hr\Payslip;
use App\Models\Hr\SalaryAdvance;
use App\Services\Hr\PayrollService;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollController extends Controller
{
    public function __construct(private readonly PayrollService $payroll) {}

    public function index(): View
    {
        HrAccess::module('payroll');

        return view('admin.hr.payroll.index', [
            'runs' => PayrollRun::withCount('payslips')->withSum('payslips', 'net_pay')->latest('month')->paginate(24),
            'defaultMonth' => \App\Support\HrClock::now()->subMonthNoOverflow()->format('Y-m'),
            'withoutSalary' => Employee::working()->whereDoesntHave('salary')->count(),
            'canCreate' => Auth::user()->can('payroll.create'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = HrAccess::ability('payroll.create');

        $data = $request->validate(['month' => ['required', 'date_format:Y-m']]);
        $run = $this->payroll->generate($data['month'], $actor);

        return redirect()->route('admin.hr.payroll.show', $run)
            ->with('status', "Draft payroll for {$run->label()} generated with {$run->payslips()->count()} payslip(s). Review it, then finalize.");
    }

    public function show(PayrollRun $payroll): View
    {
        HrAccess::module('payroll');

        $payslips = $payroll->payslips()->with(['employee.user', 'employee.department'])->get()
            ->sortBy(fn (Payslip $p) => $p->employee->employee_code)->values();

        return view('admin.hr.payroll.show', [
            'run' => $payroll,
            'payslips' => $payslips,
            'totals' => [
                'gross' => $payslips->sum('earned_gross'),
                'deductions' => $payslips->sum('total_deductions'),
                'net' => $payslips->sum('net_pay'),
            ],
            'canCreate' => Auth::user()->can('payroll.create'),
            'canApprove' => Auth::user()->can('payroll.approve'),
            'canExport' => Auth::user()->can('payroll.export'),
        ]);
    }

    public function adjust(Request $request, Payslip $payslip): RedirectResponse
    {
        HrAccess::ability('payroll.create');

        $data = $request->validate([
            'bonus' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'incentive' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'overtime' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'other_deduction' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
        ]);

        $this->payroll->adjust($payslip, $data);

        return back()->with('status', "Payslip of {$payslip->employee->name} updated.");
    }

    public function finalize(PayrollRun $payroll): RedirectResponse
    {
        $actor = HrAccess::ability('payroll.approve');

        $this->payroll->finalize($payroll, $actor);

        return back()->with('status', "Payroll for {$payroll->label()} finalized. Employees have been emailed that their payslips are ready.");
    }

    public function destroy(PayrollRun $payroll): RedirectResponse
    {
        HrAccess::ability('payroll.create');

        $this->payroll->deleteDraft($payroll);

        return redirect()->route('admin.hr.payroll.index')->with('status', 'Draft payroll deleted.');
    }

    public function export(PayrollRun $payroll): StreamedResponse
    {
        HrAccess::ability('payroll.export');

        $payslips = $payroll->payslips()->with(['employee.user'])->get()->sortBy(fn ($p) => $p->employee->employee_code);

        return response()->streamDownload(function () use ($payslips) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee ID', 'Name', 'Bank Account', 'IFSC', 'Days', 'Paid Days', 'LOP Days',
                'Basic', 'HRA', 'Conveyance', 'Special', 'Other', 'Monthly Gross', 'LOP Deduction', 'Bonus', 'Incentive', 'Overtime', 'Earned Gross',
                'PF', 'ESI', 'Prof. Tax', 'TDS', 'Advance', 'Other Deduction', 'Total Deductions', 'Net Pay']);

            foreach ($payslips as $p) {
                fputcsv($out, [
                    $p->employee->employee_code, $p->employee->name, $p->employee->bank_account_number, $p->employee->bank_ifsc,
                    $p->days_in_month, $p->paid_days, $p->lop_days,
                    $p->basic, $p->hra, $p->conveyance, $p->special_allowance, $p->other_allowance, $p->gross_earnings, $p->lop_deduction,
                    $p->bonus, $p->incentive, $p->overtime, $p->earned_gross,
                    $p->pf, $p->esi, $p->professional_tax, $p->tds, $p->advance_deduction, $p->other_deduction, $p->total_deductions, $p->net_pay,
                ]);
            }

            fclose($out);
        }, 'salary-register-'.$payroll->month.'.csv', ['Content-Type' => 'text/csv']);
    }

    public function payslip(Payslip $payslip): View
    {
        HrAccess::module('payroll');

        return view('hr.payslip', ['payslip' => $payslip->load(['run', 'employee.user', 'employee.department', 'employee.designation'])]);
    }

    public function advances(): View
    {
        HrAccess::module('payroll');

        return view('admin.hr.payroll.advances', [
            'advances' => SalaryAdvance::with('employee.user')->orderByRaw("status = 'active' desc")->latest('issued_on')->paginate(25),
            'employees' => Employee::working()->with('user')->orderBy('employee_code')->get(),
            'canCreate' => Auth::user()->can('payroll.create'),
        ]);
    }

    public function storeAdvance(Request $request): RedirectResponse
    {
        $actor = HrAccess::ability('payroll.create');

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:9999999'],
            'monthly_deduction' => ['required', 'numeric', 'min:1', 'max:9999999'],
            'issued_on' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->payroll->issueAdvance(Employee::findOrFail($data['employee_id']), (float) $data['amount'],
            (float) $data['monthly_deduction'], $data['reason'] ?? null, $data['issued_on'], $actor);

        return back()->with('status', 'Advance recorded. It will be recovered from upcoming payrolls.');
    }

    public function closeAdvance(SalaryAdvance $advance): RedirectResponse
    {
        HrAccess::ability('payroll.create');

        $advance->update(['status' => 'closed']);

        return back()->with('status', 'Advance closed. No further deductions will be made.');
    }
}
