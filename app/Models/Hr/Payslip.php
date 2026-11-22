<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'payroll_run_id', 'employee_id', 'days_in_month', 'paid_days', 'lop_days',
    'basic', 'hra', 'conveyance', 'special_allowance', 'other_allowance', 'gross_earnings',
    'lop_deduction', 'bonus', 'incentive', 'overtime', 'earned_gross',
    'pf', 'esi', 'professional_tax', 'tds', 'advance_deduction', 'other_deduction',
    'total_deductions', 'net_pay', 'emailed_at',
])]
class Payslip extends Model
{
    public const MONEY = [
        'basic', 'hra', 'conveyance', 'special_allowance', 'other_allowance', 'gross_earnings',
        'lop_deduction', 'bonus', 'incentive', 'overtime', 'earned_gross',
        'pf', 'esi', 'professional_tax', 'tds', 'advance_deduction', 'other_deduction',
        'total_deductions', 'net_pay',
    ];

    protected function casts(): array
    {
        return array_merge(
            array_fill_keys(self::MONEY, 'float'),
            ['paid_days' => 'float', 'lop_days' => 'float', 'emailed_at' => 'datetime'],
        );
    }

    /** @return BelongsTo<PayrollRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
