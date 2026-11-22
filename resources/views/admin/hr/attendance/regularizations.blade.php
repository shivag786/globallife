<x-layouts.app title="Attendance Corrections" heading="Attendance Correction Requests">
    @include('admin.hr.partials.status-tabs', ['route' => 'admin.hr.regularizations.index', 'current' => $status, 'tabs' => ['pending', 'approved', 'rejected', 'all']])

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr>
                        <th class="px-3 py-3">Employee</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Requested time</th>
                        <th class="px-3 py-3">Reason</th><th class="px-3 py-3">Status</th><th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $r)
                        <tr class="border-top align-top">
                            <td class="px-3 py-2"><strong>{{ $r->employee->name }}</strong><div class="fs-xs text-secondary">{{ $r->employee->employee_code }}</div></td>
                            <td class="px-3 py-2">{{ $r->date->format('d M Y') }}</td>
                            <td class="px-3 py-2">{{ substr($r->check_in_time, 0, 5) }} - {{ substr($r->check_out_time, 0, 5) }}</td>
                            <td class="px-3 py-2" style="max-width: 20rem">{{ $r->reason }}</td>
                            <td class="px-3 py-2">
                                @include('hr.partials.badge', ['status' => $r->status])
                                @if ($r->reviewer) <div class="fs-xs text-secondary">by {{ $r->reviewer->name }}</div> @endif
                                @if ($r->review_note) <div class="fs-xs text-secondary">{{ $r->review_note }}</div> @endif
                            </td>
                            <td class="px-3 py-2">
                                @if ($r->status === 'pending' && $canApprove)
                                    @include('admin.hr.partials.review-buttons', [
                                        'approve' => route('admin.hr.regularizations.approve', $r),
                                        'reject' => route('admin.hr.regularizations.reject', $r),
                                    ])
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">Nothing here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $requests->links() }}</div>
</x-layouts.app>
