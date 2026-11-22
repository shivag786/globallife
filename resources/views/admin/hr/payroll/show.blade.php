<x-layouts.app title="Payroll {{ $run->label() }}" heading="Payroll - {{ $run->label() }}">
    @php $m = fn ($v) => number_format((float) $v, 2); @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            @include('hr.partials.badge', ['status' => $run->status])
            @if (! $run->isDraft())
                <span class="small text-secondary">Finalized {{ $run->finalized_at?->format('d M Y, h:i A') }} by {{ $run->finalizer?->name }}</span>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if ($canExport)
                <a href="{{ route('admin.hr.payroll.export', $run) }}" class="btn btn-sm btn-outline-secondary">Salary register (CSV)</a>
            @endif
            @if ($run->isDraft())
                @if ($canCreate)
                    <form method="POST" action="{{ route('admin.hr.payroll.store') }}">
                        @csrf <input type="hidden" name="month" value="{{ $run->month }}">
                        <button class="btn btn-sm btn-outline-secondary">Recalculate</button>
                    </form>
                    <form method="POST" action="{{ route('admin.hr.payroll.destroy', $run) }}" onsubmit="return confirm('Delete this draft payroll?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete draft</button>
                    </form>
                @endif
                @if ($canApprove)
                    <form method="POST" action="{{ route('admin.hr.payroll.finalize', $run) }}"
                          onsubmit="return confirm('Finalize payroll for {{ $run->label() }}? Payslips get locked, advances are recovered and every employee is emailed.')">
                        @csrf
                        <button class="btn btn-sm btn-brand">Finalize &amp; email payslips</button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <div class="d-grid grid-cols-1 grid-cols-md-3 gap-4 mb-4">
        <x-stat-tile label="Earned Gross" :value="$totals['gross']" money icon="rupee" color="sky" />
        <x-stat-tile label="Deductions" :value="$totals['deductions']" money icon="minus" color="rose" />
        <x-stat-tile label="Net Payout" :value="$totals['net']" money icon="rupee" color="emerald" :hint="$payslips->count().' employees'" />
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm text-nowrap">
                <thead class="bg-body-tertiary text-secondary">
                    <tr>
                        <th class="px-3 py-3">Employee</th>
                        <th class="px-3 py-3 text-end">Paid days</th>
                        <th class="px-3 py-3 text-end">Gross</th>
                        <th class="px-3 py-3 text-end">LOP</th>
                        <th class="px-3 py-3 text-end">Bonus etc.</th>
                        <th class="px-3 py-3 text-end">Earned</th>
                        <th class="px-3 py-3 text-end">PF / ESI / PT / TDS</th>
                        <th class="px-3 py-3 text-end">Advance + Other</th>
                        <th class="px-3 py-3 text-end">Net pay</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payslips as $p)
                        <tr class="border-top">
                            <td class="px-3 py-2"><strong>{{ $p->employee->name }}</strong><div class="fs-xs text-secondary">{{ $p->employee->employee_code }}</div></td>
                            <td class="px-3 py-2 text-end">{{ $p->paid_days }} / {{ $p->days_in_month }}</td>
                            <td class="px-3 py-2 text-end">{{ $m($p->gross_earnings) }}</td>
                            <td class="px-3 py-2 text-end text-danger">{{ $p->lop_deduction > 0 ? '-'.$m($p->lop_deduction) : '-' }}</td>
                            <td class="px-3 py-2 text-end">{{ $m($p->bonus + $p->incentive + $p->overtime) }}</td>
                            <td class="px-3 py-2 text-end">{{ $m($p->earned_gross) }}</td>
                            <td class="px-3 py-2 text-end fs-xs">{{ $m($p->pf) }} / {{ $m($p->esi) }} / {{ $m($p->professional_tax) }} / {{ $m($p->tds) }}</td>
                            <td class="px-3 py-2 text-end">{{ $m($p->advance_deduction + $p->other_deduction) }}</td>
                            <td class="px-3 py-2 text-end fw-bold">{{ $m($p->net_pay) }}</td>
                            <td class="px-3 py-2 text-end">
                                <a href="{{ route('admin.hr.payslips.show', $p) }}" target="_blank" class="text-primary">Payslip</a>
                                @if ($run->isDraft() && $canCreate)
                                    <button class="btn btn-sm btn-link p-0 ms-2" data-bs-toggle="collapse" data-bs-target="#adj-{{ $p->id }}">Adjust</button>
                                @endif
                            </td>
                        </tr>
                        @if ($run->isDraft() && $canCreate)
                            <tr class="collapse" id="adj-{{ $p->id }}">
                                <td colspan="10" class="px-3 pb-3">
                                    <form method="POST" action="{{ route('admin.hr.payslips.adjust', $p) }}" class="row g-2 align-items-end">
                                        @csrf @method('PATCH')
                                        @foreach (['bonus' => 'Bonus', 'incentive' => 'Incentive', 'overtime' => 'Overtime', 'other_deduction' => 'Other deduction'] as $k => $label)
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small">{{ $label }}</label>
                                                <input type="number" step="0.01" min="0" name="{{ $k }}" value="{{ $p->{$k} }}" class="form-control form-control-sm">
                                            </div>
                                        @endforeach
                                        <div class="col-md-2"><button class="btn btn-sm btn-brand w-100">Recalculate</button></div>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="10" class="px-4 py-6 text-center text-secondary">No payslips. Add salary structures to employees and recalculate.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
