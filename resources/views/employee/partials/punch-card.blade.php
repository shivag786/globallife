{{-- Today's check-in / check-out. Needs $today (Attendance|null). --}}
<x-dash-card title="Today's attendance" subtitle="{{ \App\Support\HrClock::now()->format('l, d M Y') }}" icon="calendar">
    @if (! empty($holidayToday))
        <p class="small text-info-emphasis mb-2">Today is a holiday: <strong>{{ $holidayToday }}</strong>.</p>
    @elseif (isset($isWorkingDay) && ! $isWorkingDay)
        <p class="small text-secondary mb-2">Today is your weekly off.</p>
    @endif

    <div class="d-flex justify-content-around text-center my-3">
        <div>
            <div class="fs-xs text-secondary text-uppercase">Check-in</div>
            <div class="h4 fw-bold mb-0">{{ $today?->check_in_at?->format('h:i A') ?? '--:--' }}</div>
            @if ($today?->is_late) <span class="badge text-bg-warning">Late</span> @endif
        </div>
        <div>
            <div class="fs-xs text-secondary text-uppercase">Check-out</div>
            <div class="h4 fw-bold mb-0">{{ $today?->check_out_at?->format('h:i A') ?? '--:--' }}</div>
            @if ($today?->check_out_at) <span class="fs-xs text-secondary">Worked {{ $today->workedLabel() }}</span> @endif
        </div>
    </div>

    @if (! $today?->check_in_at)
        <form method="POST" action="{{ route('employee.attendance.check-in') }}">
            @csrf
            <button class="btn btn-success w-100 py-2 fw-semibold">Check in now</button>
        </form>
    @elseif (! $today->check_out_at)
        <form method="POST" action="{{ route('employee.attendance.check-out') }}" onsubmit="return confirm('Check out for today?')">
            @csrf
            <button class="btn btn-danger w-100 py-2 fw-semibold">Check out</button>
        </form>
    @else
        <div class="alert alert-success small mb-0 text-center">Attendance completed for today
            ({{ \App\Models\Hr\Attendance::STATUSES[$today->status] ?? $today->status }}).</div>
    @endif
</x-dash-card>
