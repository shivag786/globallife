<x-layouts.app title="My Payslips" heading="My Payslips">
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-secondary">
                <tr><th class="px-4 py-3">Month</th><th class="px-4 py-3">Paid days</th><th class="px-4 py-3 text-end">Earnings</th><th class="px-4 py-3 text-end">Deductions</th><th class="px-4 py-3 text-end">Net pay</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($payslips as $p)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $p->run->label() }}</td>
                        <td class="px-4 py-3">{{ $p->paid_days }} / {{ $p->days_in_month }}</td>
                        <td class="px-4 py-3 text-end">{{ number_format($p->earned_gross, 2) }}</td>
                        <td class="px-4 py-3 text-end">{{ number_format($p->total_deductions, 2) }}</td>
                        <td class="px-4 py-3 text-end fw-bold">Rs. {{ number_format($p->net_pay, 2) }}</td>
                        <td class="px-4 py-3 text-end"><a href="{{ route('employee.payslips.show', $p) }}" target="_blank" class="btn btn-sm btn-outline-secondary">View / PDF</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">No payslips yet. They appear here once HR finalizes the month's payroll.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
