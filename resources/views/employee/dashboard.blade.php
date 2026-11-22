<x-layouts.app title="Employee Dashboard" heading="My Dashboard">
    <x-dash-hero :name="$employee->name" eyebrow="{{ $employee->designation?->name ?? 'Employee' }} · {{ $employee->employee_code }}"
                 subtitle="{{ $employee->department?->name ? $employee->department->name.' department' : 'Welcome to your employee portal.' }}">
        <x-slot:meta>
            @if ($shift)
                <span class="dash-hero__chip"><x-icon name="calendar" class="w-3.5 h-3.5" /> {{ $shift->label() }}</span>
            @endif
            @if ($employee->reportingManager)
                <span class="dash-hero__chip"><x-icon name="user" class="w-3.5 h-3.5" /> Reports to {{ $employee->reportingManager->name }}</span>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('employee.leaves.index') }}" class="dash-hero__btn dash-hero__btn--gold">
                <x-icon name="calendar" class="w-4 h-4" /> Apply for leave
            </a>
            <a href="{{ route('employee.payslips.index') }}" class="dash-hero__btn dash-hero__btn--ghost">
                Payslips <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </x-slot:actions>
    </x-dash-hero>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            @include('employee.partials.punch-card')
        </div>
        <div class="col-lg-7">
            <x-dash-card title="This month" subtitle="{{ \App\Support\HrClock::now()->format('F Y') }}" icon="check-circle">
                <div class="d-grid grid-cols-2 grid-cols-md-4 gap-3 text-center">
                    <div><div class="h3 fw-bold mb-0 text-success">{{ $monthSummary['present'] }}</div><div class="fs-xs text-secondary">Present</div></div>
                    <div><div class="h3 fw-bold mb-0 text-danger">{{ $monthSummary['absent'] }}</div><div class="fs-xs text-secondary">Absent</div></div>
                    <div><div class="h3 fw-bold mb-0 text-info">{{ $monthSummary['leave'] + $monthSummary['unpaid_leave'] }}</div><div class="fs-xs text-secondary">On leave</div></div>
                    <div><div class="h3 fw-bold mb-0 text-warning">{{ $monthSummary['late'] }}</div><div class="fs-xs text-secondary">Late</div></div>
                </div>
                <a href="{{ route('employee.attendance.index') }}" class="small d-inline-block mt-3">See my attendance calendar &rarr;</a>
            </x-dash-card>
        </div>
    </div>

    @if ($teamPending > 0)
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span><strong>{{ $teamPending }}</strong> leave request(s) from your team are waiting for your approval.</span>
            <a href="{{ route('employee.team-leaves.index') }}" class="btn btn-sm btn-warning">Review</a>
        </div>
    @endif

    <x-dash-heading title="Leave balance" subtitle="{{ \App\Support\HrClock::now()->year }}{{ $pendingLeaves ? ' · '.$pendingLeaves.' request(s) pending' : '' }}" icon="calendar" />
    <div class="d-grid grid-cols-2 grid-cols-md-4 gap-4 mb-6">
        @forelse ($balances as $b)
            <x-stat-tile :label="$b['type']->name" :value="$b['available']" icon="calendar" color="sky"
                         :hint="'of '.$b['balance']->total().' days · used '.$b['balance']->used" />
        @empty
            <p class="text-secondary small">No leave quota set up yet.</p>
        @endforelse
    </div>

    <div class="d-grid grid-cols-lg-3 gap-4">
        <x-dash-card title="Notice board" icon="megaphone">
            @forelse ($announcements as $a)
                <div class="border-bottom py-2">
                    <div class="fw-semibold small">{{ $a->title }}</div>
                    <div class="fs-xs text-secondary mb-1">{{ $a->created_at->format('d M Y') }}</div>
                    <div class="small" style="white-space: pre-line">{{ $a->body }}</div>
                </div>
            @empty
                <p class="small text-secondary mb-0">No announcements.</p>
            @endforelse
        </x-dash-card>

        <x-dash-card title="Upcoming holidays" icon="sparkles">
            @forelse ($holidays as $h)
                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <span>{{ $h->name }}</span><span class="text-secondary">{{ $h->date->format('D, d M') }}</span>
                </div>
            @empty
                <p class="small text-secondary mb-0">No upcoming holidays listed.</p>
            @endforelse
        </x-dash-card>

        <x-dash-card title="Latest payslip" icon="rupee">
            @if ($latestPayslip)
                <p class="mb-1 fw-semibold">{{ $latestPayslip->run->label() }}</p>
                <p class="h4 fw-bold">Rs. {{ number_format($latestPayslip->net_pay, 2) }}</p>
                <a href="{{ route('employee.payslips.show', $latestPayslip) }}" target="_blank" class="btn btn-sm btn-outline-secondary">View / download</a>
            @else
                <p class="small text-secondary mb-0">No payslips yet.</p>
            @endif
        </x-dash-card>
    </div>
</x-layouts.app>
