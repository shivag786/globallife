<x-layouts.app title="Team Leave Approvals" heading="Team Leave Approvals">
    <p class="small text-secondary">Your team: {{ $team->map(fn ($e) => $e->name)->implode(', ') ?: 'nobody yet' }}</p>

    <h2 class="h6 fw-semibold">Waiting for you ({{ $pending->count() }})</h2>
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden mb-5">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr><th class="px-3 py-3">Employee</th><th class="px-3 py-3">Leave</th><th class="px-3 py-3">Reason</th><th class="px-3 py-3"></th></tr>
                </thead>
                <tbody>
                    @forelse ($pending as $r)
                        <tr class="border-top align-top">
                            <td class="px-3 py-2"><strong>{{ $r->employee->name }}</strong><div class="fs-xs text-secondary">{{ $r->employee->employee_code }}</div></td>
                            <td class="px-3 py-2">{{ $r->leaveType->name }}<div class="fs-xs">{{ $r->periodLabel() }} &middot; {{ $r->days }} day(s)</div></td>
                            <td class="px-3 py-2" style="max-width: 20rem">{{ $r->reason }}</td>
                            <td class="px-3 py-2">
                                @include('admin.hr.partials.review-buttons', [
                                    'approve' => route('employee.team-leaves.approve', $r),
                                    'reject' => route('employee.team-leaves.reject', $r),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-secondary">Nothing waiting.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <h2 class="h6 fw-semibold">Recently decided</h2>
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <table class="w-100 fs-sm">
            <tbody>
                @forelse ($recent as $r)
                    <tr class="border-top">
                        <td class="px-3 py-2">{{ $r->employee->name }}</td>
                        <td class="px-3 py-2">{{ $r->leaveType->code }} &middot; {{ $r->periodLabel() }}</td>
                        <td class="px-3 py-2">@include('hr.partials.badge', ['status' => $r->status])</td>
                        <td class="px-3 py-2 fs-xs text-secondary">{{ $r->reviewer?->name }}</td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-4 text-center text-secondary">None yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
