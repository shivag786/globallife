<x-layouts.app title="Expense Claims" heading="Expense Claims">
    <div class="d-flex flex-wrap justify-content-between gap-2">
        @include('admin.hr.partials.status-tabs', ['route' => 'admin.hr.expenses.index', 'current' => $status, 'tabs' => ['pending', 'approved', 'paid', 'rejected', 'all']])
        <div class="small text-secondary">
            Waiting: <strong>Rs. {{ number_format((float) $totals['pending'], 2) }}</strong> &middot;
            Approved, to pay: <strong>Rs. {{ number_format((float) $totals['approved'], 2) }}</strong>
        </div>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr><th class="px-3 py-3">Employee</th><th class="px-3 py-3">Claim</th><th class="px-3 py-3 text-end">Amount</th><th class="px-3 py-3">Receipt</th><th class="px-3 py-3">Status</th><th class="px-3 py-3"></th></tr>
                </thead>
                <tbody>
                    @forelse ($claims as $c)
                        <tr class="border-top align-top">
                            <td class="px-3 py-2"><strong>{{ $c->employee->name }}</strong><div class="fs-xs text-secondary">{{ $c->employee->employee_code }}</div></td>
                            <td class="px-3 py-2" style="max-width: 22rem">
                                <strong>{{ $c->title }}</strong>
                                <div class="fs-xs text-secondary">{{ $c->category }} &middot; {{ $c->expense_date->format('d M Y') }}</div>
                                @if ($c->description) <div class="fs-xs">{{ $c->description }}</div> @endif
                            </td>
                            <td class="px-3 py-2 text-end fw-semibold">{{ number_format($c->amount, 2) }}</td>
                            <td class="px-3 py-2">
                                @if ($c->receipt_path) <a href="{{ route('hr.files.receipt', $c) }}" target="_blank">View</a> @else <span class="text-secondary">-</span> @endif
                            </td>
                            <td class="px-3 py-2">
                                @include('hr.partials.badge', ['status' => $c->status])
                                @if ($c->reviewer) <div class="fs-xs text-secondary">by {{ $c->reviewer->name }}</div> @endif
                                @if ($c->review_note) <div class="fs-xs text-secondary">{{ $c->review_note }}</div> @endif
                            </td>
                            <td class="px-3 py-2">
                                @can('expenses.approve')
                                    @if ($c->status === 'pending')
                                        @include('admin.hr.partials.review-buttons', ['approve' => route('admin.hr.expenses.approve', $c), 'reject' => route('admin.hr.expenses.reject', $c)])
                                    @elseif ($c->status === 'approved')
                                        <form method="POST" action="{{ route('admin.hr.expenses.paid', $c) }}">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-success">Mark paid</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">No claims.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $claims->links() }}</div>
</x-layouts.app>
