<?php

namespace App\Services\Hr;

use App\Models\Hr\Employee;
use App\Models\Hr\PayrollRun;
use App\Models\Hr\Payslip;
use App\Models\Hr\SalaryAdvance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Monthly payroll.
 *
 * generate() builds (or rebuilds) a DRAFT run for a month that has ended:
 * one payslip per employee who worked any part of that month and has a
 * salary structure. HR can then add bonus / incentive / overtime / other
 * deductions per payslip. finalize() locks the run, recovers advance
 * instalments and emails every employee that their payslip is ready.
 *
 * Calculation, per payslip:
 *  - paid days  = days paid per WorkCalendarService (present, paid leave,
 *                 holidays, weekly offs; half days count 0.5)
 *  - LOP        = monthly gross × (days in month − paid days) / days in month
 *                 (so days before joining / after leaving are unpaid too)
 *  - PF         = 12% of earned basic, basic capped at ₹15,000
 *  - ESI        = 0.75% of earned gross, only when monthly gross ≤ ₹21,000
 *  - PT, TDS    = fixed monthly amounts from the salary structure
 *  - advances   = the monthly instalment of every active advance
 */
class PayrollService
{
    public const PF_RATE = 0.12;

    public const PF_WAGE_CAP = 15000;

    public const ESI_RATE = 0.0075;

    public const ESI_GROSS_LIMIT = 21000;

    /** Payslip fields HR may adjust on a draft. */
    public const ADJUSTMENTS = ['bonus', 'incentive', 'overtime', 'other_deduction'];

    public function __construct(
        private readonly WorkCalendarService $calendar,
        private readonly HrMailService $mail,
    ) {}

    public function generate(string $month, User $actor): PayrollRun
    {
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth();

        if ($end->gte(\App\Support\HrClock::today())) {
            throw ValidationException::withMessages(['month' => 'Payroll can be generated only after the month has ended.']);
        }

        return DB::transaction(function () use ($month, $start, $end, $actor) {
            $run = PayrollRun::lockForUpdate()->firstOrCreate(['month' => $month], ['status' => 'draft', 'created_by' => $actor->id]);

            if (! $run->isDraft()) {
                throw ValidationException::withMessages(['month' => "Payroll for {$run->label()} is already finalized."]);
            }

            $employees = Employee::with(['salary', 'shift', 'user'])
                ->whereHas('salary')
                ->whereDate('joining_date', '<=', $end)
                ->where(fn ($q) => $q->whereNull('exit_date')->orWhereDate('exit_date', '>=', $start))
                ->get();

            $keep = [];
            foreach ($employees as $employee) {
                $existing = $run->payslips()->where('employee_id', $employee->id)->first();
                $adjustments = $existing ? $existing->only(self::ADJUSTMENTS) : array_fill_keys(self::ADJUSTMENTS, 0);

                $payslip = $run->payslips()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    $this->calculate($employee, $start, $adjustments),
                );
                $keep[] = $payslip->id;
            }

            // Anyone who no longer qualifies (salary removed, joining date moved).
            $run->payslips()->whereNotIn('id', $keep)->delete();

            return $run;
        });
    }

    /**
     * @param  array<string, mixed>  $adjustments
     */
    public function adjust(Payslip $payslip, array $adjustments): Payslip
    {
        if (! $payslip->run->isDraft()) {
            throw ValidationException::withMessages(['payslip' => 'This payroll is finalized and can no longer be changed.']);
        }

        $values = array_map(fn ($v) => round(max(0, (float) $v), 2), array_intersect_key($adjustments, array_flip(self::ADJUSTMENTS)));

        $payslip->update($this->calculate($payslip->employee, $payslip->run->start(), [...$payslip->only(self::ADJUSTMENTS), ...$values]));

        return $payslip;
    }

    public function finalize(PayrollRun $run, User $actor): void
    {
        DB::transaction(function () use ($run, $actor) {
            $run = PayrollRun::lockForUpdate()->findOrFail($run->id);

            if (! $run->isDraft()) {
                throw ValidationException::withMessages(['month' => 'This payroll is already finalized.']);
            }

            if (! $run->payslips()->exists()) {
                throw ValidationException::withMessages(['month' => 'There are no payslips in this payroll.']);
            }

            foreach ($run->payslips()->where('advance_deduction', '>', 0)->get() as $payslip) {
                $this->recoverAdvances($payslip->employee_id, $payslip->advance_deduction);
            }

            $run->update(['status' => 'finalized', 'finalized_by' => $actor->id, 'finalized_at' => \App\Support\HrClock::now()]);
        });

        foreach ($run->payslips()->with('employee.user')->get() as $payslip) {
            $sent = $this->mail->notice($payslip->employee->user, 'Your payslip for '.$run->label().' is ready', [
                'Your salary slip for '.$run->label().' has been generated.',
                'Net pay: Rs. '.number_format($payslip->net_pay, 2),
                'You can view and download it from the employee portal.',
            ], 'View payslip', route('employee.payslips.show', $payslip));

            if ($sent) {
                $payslip->update(['emailed_at' => \App\Support\HrClock::now()]);
            }
        }
    }

    public function deleteDraft(PayrollRun $run): void
    {
        if (! $run->isDraft()) {
            throw ValidationException::withMessages(['month' => 'A finalized payroll cannot be deleted.']);
        }

        $run->delete();
    }

    /**
     * @param  array<string, mixed>  $adjustments
     * @return array<string, mixed>
     */
    public function calculate(Employee $employee, Carbon $monthStart, array $adjustments): array
    {
        $salary = $employee->salary;
        $daysInMonth = $monthStart->daysInMonth;
        $summary = $this->calendar->summarize($this->calendar->month($employee, $monthStart));

        $paidDays = min($daysInMonth, $summary['paid']);
        $ratio = $paidDays / $daysInMonth;

        $gross = $salary->gross();
        $lop = round($gross * (1 - $ratio), 2);

        $bonus = (float) ($adjustments['bonus'] ?? 0);
        $incentive = (float) ($adjustments['incentive'] ?? 0);
        $overtime = (float) ($adjustments['overtime'] ?? 0);
        $otherDeduction = (float) ($adjustments['other_deduction'] ?? 0);

        $earnedGross = round($gross - $lop + $bonus + $incentive + $overtime, 2);
        $earnedBasic = $salary->basic * $ratio;

        $pf = $salary->pf_enabled ? round(min($earnedBasic, self::PF_WAGE_CAP) * self::PF_RATE) : 0;
        $esi = $salary->esi_enabled && $gross <= self::ESI_GROSS_LIMIT ? ceil($earnedGross * self::ESI_RATE) : 0;
        $pt = $paidDays > 0 ? $salary->professional_tax : 0;
        $tds = $paidDays > 0 ? $salary->tds : 0;
        $advance = $this->dueAdvance($employee);

        $totalDeductions = round($pf + $esi + $pt + $tds + $advance + $otherDeduction, 2);

        return [
            'days_in_month' => $daysInMonth,
            'paid_days' => $paidDays,
            'lop_days' => max(0, $daysInMonth - $paidDays),
            'basic' => $salary->basic,
            'hra' => $salary->hra,
            'conveyance' => $salary->conveyance,
            'special_allowance' => $salary->special_allowance,
            'other_allowance' => $salary->other_allowance,
            'gross_earnings' => $gross,
            'lop_deduction' => $lop,
            'bonus' => $bonus,
            'incentive' => $incentive,
            'overtime' => $overtime,
            'earned_gross' => $earnedGross,
            'pf' => $pf,
            'esi' => $esi,
            'professional_tax' => $pt,
            'tds' => $tds,
            'advance_deduction' => $advance,
            'other_deduction' => $otherDeduction,
            'total_deductions' => $totalDeductions,
            'net_pay' => max(0, round($earnedGross - $totalDeductions, 2)),
        ];
    }

    public function issueAdvance(Employee $employee, float $amount, float $monthlyDeduction, ?string $reason, string $issuedOn, User $actor): SalaryAdvance
    {
        if ($monthlyDeduction > $amount) {
            throw ValidationException::withMessages(['monthly_deduction' => 'The monthly deduction cannot be more than the advance amount.']);
        }

        return $employee->advances()->create([
            'amount' => $amount,
            'monthly_deduction' => $monthlyDeduction,
            'balance' => $amount,
            'reason' => $reason,
            'issued_on' => $issuedOn,
            'status' => 'active',
            'created_by' => $actor->id,
        ]);
    }

    private function dueAdvance(Employee $employee): float
    {
        return round($employee->advances()->where('status', 'active')->get()
            ->sum(fn (SalaryAdvance $a) => min($a->monthly_deduction, $a->balance)), 2);
    }

    /** Apply a payslip's advance deduction to the oldest advances first. */
    private function recoverAdvances(int $employeeId, float $amount): void
    {
        $advances = SalaryAdvance::where('employee_id', $employeeId)->where('status', 'active')
            ->orderBy('issued_on')->orderBy('id')->lockForUpdate()->get();

        foreach ($advances as $advance) {
            if ($amount <= 0) {
                break;
            }

            $take = min($advance->monthly_deduction, $advance->balance, $amount);
            $balance = round($advance->balance - $take, 2);
            $amount = round($amount - $take, 2);

            $advance->update(['balance' => $balance, 'status' => $balance <= 0 ? 'closed' : 'active']);
        }
    }
}
