<x-layouts.app title="Payroll" heading="Payroll">
    <div class="row g-4">
        @if ($canCreate)
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">Run payroll</div>
                    <form method="POST" action="{{ route('admin.hr.payroll.store') }}" class="card-body d-flex flex-column gap-3">
                        @csrf
                        <div>
                            <label class="form-label small fw-medium">Month</label>
                            <input type="month" name="month" value="{{ old('month', $defaultMonth) }}" max="{{ \App\Support\HrClock::now()->subMonthNoOverflow()->format('Y-m') }}"
                                   class="form-control @error('month') is-invalid @enderror" required>
                            @error('month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <p class="small text-secondary mb-0">
                            Builds a <strong>draft</strong> from attendance, approved leave, holidays and each employee's salary structure.
                            Running it again for a draft month recalculates it (bonus / deductions you added are kept).
                        </p>
                        @if ($withoutSalary > 0)
                            <div class="alert alert-warning small mb-0">{{ $withoutSalary }} current employee(s) have no salary structure and will be left out.</div>
                        @endif
                        <button class="btn btn-brand align-self-start">Generate draft</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="{{ $canCreate ? 'col-lg-8' : 'col-12' }}">
            <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
                <table class="w-100 fs-sm">
                    <thead class="bg-body-tertiary text-secondary">
                        <tr><th class="px-4 py-3">Month</th><th class="px-4 py-3">Payslips</th><th class="px-4 py-3">Net payout</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($runs as $run)
                            <tr class="border-top">
                                <td class="px-4 py-3 fw-medium">{{ $run->label() }}</td>
                                <td class="px-4 py-3">{{ $run->payslips_count }}</td>
                                <td class="px-4 py-3">Rs. {{ number_format((float) $run->payslips_sum_net_pay, 2) }}</td>
                                <td class="px-4 py-3">@include('hr.partials.badge', ['status' => $run->status])</td>
                                <td class="px-4 py-3 text-end"><a href="{{ route('admin.hr.payroll.show', $run) }}" class="text-primary hover-underline">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No payroll yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $runs->links() }}</div>
        </div>
    </div>
</x-layouts.app>
