<x-layouts.app title="Salary Advances" heading="Salary Advances & Loans">
    @if ($canCreate)
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-semibold">Give an advance</div>
            <form method="POST" action="{{ route('admin.hr.advances.store') }}" class="card-body row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small">Employee</label>
                    <select name="employee_id" class="form-select form-select-sm" required>
                        <option value="">Select...</option>
                        @foreach ($employees as $e) <option value="{{ $e->id }}" @selected(old('employee_id') == $e->id)>{{ $e->name }} ({{ $e->employee_code }})</option> @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label small">Amount</label><input type="number" step="0.01" min="1" name="amount" value="{{ old('amount') }}" class="form-control form-control-sm" required></div>
                <div class="col-md-2"><label class="form-label small">Monthly EMI</label><input type="number" step="0.01" min="1" name="monthly_deduction" value="{{ old('monthly_deduction') }}" class="form-control form-control-sm" required></div>
                <div class="col-md-2"><label class="form-label small">Given on</label><input type="date" name="issued_on" value="{{ old('issued_on', \App\Support\HrClock::today()->format('Y-m-d')) }}" class="form-control form-control-sm" required></div>
                <div class="col-md-2"><label class="form-label small">Reason</label><input name="reason" value="{{ old('reason') }}" class="form-control form-control-sm"></div>
                <div class="col-md-1"><button class="btn btn-brand btn-sm w-100">Save</button></div>
                <div class="col-12 fs-xs text-secondary">The EMI is deducted in every payroll until the balance is recovered.</div>
            </form>
        </div>
    @endif

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-secondary">
                <tr><th class="px-4 py-3">Employee</th><th class="px-4 py-3">Given</th><th class="px-4 py-3 text-end">Amount</th><th class="px-4 py-3 text-end">EMI</th><th class="px-4 py-3 text-end">Balance</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($advances as $a)
                    <tr class="border-top">
                        <td class="px-4 py-3"><strong>{{ $a->employee->name }}</strong><div class="fs-xs text-secondary">{{ $a->reason }}</div></td>
                        <td class="px-4 py-3">{{ $a->issued_on->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-end">{{ number_format($a->amount, 2) }}</td>
                        <td class="px-4 py-3 text-end">{{ number_format($a->monthly_deduction, 2) }}</td>
                        <td class="px-4 py-3 text-end fw-semibold">{{ number_format($a->balance, 2) }}</td>
                        <td class="px-4 py-3">@include('hr.partials.badge', ['status' => $a->status])</td>
                        <td class="px-4 py-3 text-end">
                            @if ($a->status === 'active' && $canCreate)
                                <form method="POST" action="{{ route('admin.hr.advances.close', $a) }}" onsubmit="return confirm('Close this advance? No more EMIs will be deducted.')">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-link text-danger p-0">Close</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-secondary">No advances.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $advances->links() }}</div>
</x-layouts.app>
