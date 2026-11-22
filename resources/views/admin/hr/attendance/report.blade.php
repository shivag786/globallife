<x-layouts.app title="Attendance Report" heading="Attendance Report - {{ $month->format('F Y') }}">
    @php
        $codes = ['present' => ['P', 'success'], 'half_day' => ['HD', 'warning'], 'absent' => ['A', 'danger'], 'leave' => ['L', 'info'],
            'unpaid_leave' => ['LWP', 'danger'], 'holiday' => ['H', 'primary'], 'weekly_off' => ['WO', 'secondary'], 'na' => ['', 'light'], 'upcoming' => ['', 'light']];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <form method="GET" class="d-flex flex-wrap gap-2">
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" max="{{ \App\Support\HrClock::now()->format('Y-m') }}" class="form-control form-control-sm">
            <select name="department" class="form-select form-select-sm" style="width: 12rem">
                <option value="">All departments</option>
                @foreach ($departments as $d)
                    <option value="{{ $d->id }}" @selected((string) request('department') === (string) $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Show</button>
        </form>
        @if ($canExport)
            <a href="{{ route('admin.hr.attendance.export', request()->only('month', 'department')) }}" class="btn btn-sm btn-outline-secondary">Export Excel (CSV)</a>
        @endif
    </div>

    <p class="fs-xs text-secondary">
        P = Present &middot; HD = Half day &middot; A = Absent &middot; L = Paid leave &middot; LWP = Unpaid leave &middot; H = Holiday &middot; WO = Weekly off.
        Late arrivals are underlined.
    </p>

    <div class="bg-body rounded-2 shadow-sm border overflow-x-auto">
        <table class="fs-xs text-center" style="min-width: 100%">
            <thead class="bg-body-tertiary text-secondary">
                <tr>
                    <th class="px-3 py-2 text-start" style="min-width: 12rem">Employee</th>
                    @for ($d = 1; $d <= $month->daysInMonth; $d++)
                        <th class="px-1 py-2">{{ $d }}<div class="fw-normal">{{ substr($month->copy()->day($d)->format('D'), 0, 2) }}</div></th>
                    @endfor
                    <th class="px-2 py-2">P</th><th class="px-2 py-2">A</th><th class="px-2 py-2">L</th><th class="px-2 py-2">Late</th><th class="px-2 py-2">Paid days</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-top">
                        <td class="px-3 py-2 text-start">
                            <strong>{{ $row['employee']->name }}</strong>
                            <div class="text-secondary">{{ $row['employee']->employee_code }}</div>
                        </td>
                        @foreach ($row['days'] as $day)
                            @php [$c, $tone] = $codes[$day['code']] ?? ['', 'light']; @endphp
                            <td class="px-1 py-2" title="{{ $day['date']->format('d M') }}: {{ $day['label'] }}">
                                @if ($c)
                                    <span class="text-{{ $tone }}-emphasis fw-semibold {{ $day['attendance']?->is_late ? 'text-decoration-underline' : '' }}">{{ $c }}</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-2 fw-semibold">{{ $row['summary']['present'] }}</td>
                        <td class="px-2">{{ $row['summary']['absent'] }}</td>
                        <td class="px-2">{{ $row['summary']['leave'] + $row['summary']['unpaid_leave'] }}</td>
                        <td class="px-2">{{ $row['summary']['late'] }}</td>
                        <td class="px-2 fw-semibold">{{ $row['summary']['paid'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="40" class="px-4 py-6 text-secondary">No employees in this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
