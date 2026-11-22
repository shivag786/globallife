<x-layouts.app title="Leave Requests" heading="Leave Requests">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
        @include('admin.hr.partials.status-tabs', ['route' => 'admin.hr.leaves.index', 'current' => $status, 'tabs' => ['pending', 'approved', 'rejected', 'cancelled', 'all']])
        <form method="GET" class="d-flex gap-2 mb-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Employee name or ID">
            <select name="type" class="form-select form-select-sm">
                <option value="">All types</option>
                @foreach ($types as $t) <option value="{{ $t->id }}" @selected((string) request('type') === (string) $t->id)>{{ $t->name }}</option> @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
        </form>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr>
                        <th class="px-3 py-3">Employee</th><th class="px-3 py-3">Type</th><th class="px-3 py-3">Dates</th>
                        <th class="px-3 py-3">Days</th><th class="px-3 py-3">Reason</th><th class="px-3 py-3">Status</th><th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $r)
                        <tr class="border-top align-top">
                            <td class="px-3 py-2">
                                <strong>{{ $r->employee->name }}</strong>
                                <div class="fs-xs text-secondary">{{ $r->employee->employee_code }} &middot; {{ $r->employee->department?->name }}</div>
                            </td>
                            <td class="px-3 py-2">{{ $r->leaveType->name }}</td>
                            <td class="px-3 py-2">{{ $r->periodLabel() }}<div class="fs-xs text-secondary">Applied {{ $r->created_at->format('d M') }}</div></td>
                            <td class="px-3 py-2">{{ $r->days }}</td>
                            <td class="px-3 py-2" style="max-width: 18rem">{{ $r->reason }}</td>
                            <td class="px-3 py-2">
                                @include('hr.partials.badge', ['status' => $r->status])
                                @if ($r->reviewer) <div class="fs-xs text-secondary">by {{ $r->reviewer->name }}</div> @endif
                                @if ($r->review_note) <div class="fs-xs text-secondary">{{ $r->review_note }}</div> @endif
                            </td>
                            <td class="px-3 py-2">
                                @if ($r->status === 'pending')
                                    @can('leaves.approve')
                                        @include('admin.hr.partials.review-buttons', [
                                            'approve' => route('admin.hr.leaves.approve', $r),
                                            'reject' => route('admin.hr.leaves.reject', $r),
                                        ])
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-secondary">No leave requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $requests->links() }}</div>
</x-layouts.app>
