@php
    $settings = app(\App\Services\SettingsService::class)->all();
    $company = $settings['site_title'] ?? config('app.name');
    $e = $payslip->employee;
    $m = fn ($v) => number_format((float) $v, 2);
    $earnings = array_filter([
        'Basic' => $payslip->basic, 'HRA' => $payslip->hra, 'Conveyance' => $payslip->conveyance,
        'Special Allowance' => $payslip->special_allowance, 'Other Allowance' => $payslip->other_allowance,
        'Bonus' => $payslip->bonus, 'Incentive' => $payslip->incentive, 'Overtime' => $payslip->overtime,
    ], fn ($v) => $v > 0);
    $deductions = array_filter([
        'Loss of Pay ('.$payslip->lop_days.' days)' => $payslip->lop_deduction, 'Provident Fund' => $payslip->pf, 'ESI' => $payslip->esi,
        'Professional Tax' => $payslip->professional_tax, 'TDS' => $payslip->tds, 'Salary Advance' => $payslip->advance_deduction,
        'Other Deduction' => $payslip->other_deduction,
    ], fn ($v) => $v > 0);
    $totalEarnings = array_sum($earnings);
    $totalDeductions = array_sum($deductions);
    $rows = max(count($earnings), count($deductions));
    $ek = array_keys($earnings); $dk = array_keys($deductions);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslip {{ $payslip->run->label() }} - {{ $e->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1f2d27; background: #eef2f0; margin: 0; padding: 24px; font-size: 13px; }
        .sheet { max-width: 800px; margin: 0 auto; background: #fff; padding: 32px; border: 1px solid #d6e2dc; }
        .head { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #1b3c2c; padding-bottom: 14px; margin-bottom: 18px; gap: 12px; }
        .head img { height: 48px; }
        h1 { font-size: 20px; margin: 0; color: #1b3c2c; }
        h2 { font-size: 15px; margin: 0; text-align: right; }
        .muted { color: #6b7d74; }
        table { width: 100%; border-collapse: collapse; }
        .info td { padding: 5px 6px; vertical-align: top; }
        .info td.l { color: #6b7d74; width: 22%; }
        .pay { margin-top: 18px; border: 1px solid #cfdcd5; }
        .pay th { background: #1b3c2c; color: #fff; text-align: left; padding: 8px; font-size: 12px; }
        .pay td { padding: 7px 8px; border-top: 1px solid #e3ebe7; }
        .pay td.n { text-align: right; white-space: nowrap; }
        .pay tr.t td { font-weight: bold; background: #f0f7f3; }
        .net { margin-top: 18px; display: flex; justify-content: space-between; align-items: center; background: #1b3c2c; color: #fff; padding: 14px 18px; font-size: 16px; }
        .net strong { font-size: 22px; }
        .foot { margin-top: 26px; font-size: 11px; text-align: center; color: #6b7d74; }
        .actions { max-width: 800px; margin: 0 auto 12px; text-align: right; }
        .actions button { background: #1b3c2c; color: #fff; border: 0; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        @media print { body { background: #fff; padding: 0; } .sheet { border: 0; } .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions"><button onclick="window.print()">Download / Print PDF</button></div>
    <div class="sheet">
        <div class="head">
            <div style="display:flex;align-items:center;gap:12px">
                @if (! empty($settings['site_logo']))
                    <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="">
                @endif
                <div>
                    <h1>{{ $company }}</h1>
                    @if (! empty($settings['contact_address'])) <div class="muted">{{ $settings['contact_address'] }}</div> @endif
                </div>
            </div>
            <div>
                <h2>Payslip</h2>
                <div class="muted" style="text-align:right">{{ $payslip->run->label() }}</div>
            </div>
        </div>

        <table class="info">
            <tr><td class="l">Employee</td><td><strong>{{ $e->name }}</strong></td><td class="l">Employee ID</td><td>{{ $e->employee_code }}</td></tr>
            <tr><td class="l">Designation</td><td>{{ $e->designation?->name ?? '-' }}</td><td class="l">Department</td><td>{{ $e->department?->name ?? '-' }}</td></tr>
            <tr><td class="l">Date of joining</td><td>{{ $e->joining_date->format('d M Y') }}</td><td class="l">PAN</td><td>{{ $e->pan_number ?: '-' }}</td></tr>
            <tr><td class="l">Bank A/c</td><td>{{ $e->bank_account_number ? $e->bank_name.' - XXXX'.substr($e->bank_account_number, -4) : '-' }}</td><td class="l">PF UAN</td><td>{{ $e->uan_number ?: '-' }}</td></tr>
            <tr><td class="l">Days in month</td><td>{{ $payslip->days_in_month }}</td><td class="l">Paid days</td><td>{{ $payslip->paid_days }} (LOP {{ $payslip->lop_days }})</td></tr>
        </table>

        <table class="pay">
            <tr><th style="width:35%">Earnings</th><th style="width:15%;text-align:right">Amount (Rs.)</th><th style="width:35%">Deductions</th><th style="width:15%;text-align:right">Amount (Rs.)</th></tr>
            @for ($i = 0; $i < $rows; $i++)
                <tr>
                    <td>{{ $ek[$i] ?? '' }}</td><td class="n">{{ isset($ek[$i]) ? $m($earnings[$ek[$i]]) : '' }}</td>
                    <td>{{ $dk[$i] ?? '' }}</td><td class="n">{{ isset($dk[$i]) ? $m($deductions[$dk[$i]]) : '' }}</td>
                </tr>
            @endfor
            <tr class="t"><td>Total earnings</td><td class="n">{{ $m($totalEarnings) }}</td><td>Total deductions</td><td class="n">{{ $m($totalDeductions) }}</td></tr>
        </table>

        <div class="net"><span>Net pay for {{ $payslip->run->label() }}</span><strong>Rs. {{ $m($payslip->net_pay) }}</strong></div>

        <p class="foot">This is a computer-generated payslip and does not require a signature.</p>
    </div>
</body>
</html>
