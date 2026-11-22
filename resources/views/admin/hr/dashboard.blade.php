<x-layouts.app title="HR Dashboard" heading="HR Dashboard">
    @php $pm = \App\Services\PermissionMatrixService::class; $u = auth()->user(); @endphp

    <x-dash-heading title="Today" subtitle="{{ \App\Support\HrClock::now()->format('l, d F Y') }}" icon="calendar" />
    <div class="d-grid grid-cols-2 grid-cols-md-4 gap-4 mb-6">
        <x-stat-tile label="Employees" :value="$today['total']" icon="users" color="brand"
            :href="$pm::userCanAccessModule($u, 'employees') ? route('admin.hr.employees.index') : null" cta="View all" />
        <x-stat-tile label="Present Today" :value="$today['present']" icon="check-circle" color="emerald"
            :hint="$today['late'].' came late'"
            :href="$pm::userCanAccessModule($u, 'attendance') ? route('admin.hr.attendance.index') : null" cta="Day sheet" />
        <x-stat-tile label="On Leave" :value="$today['on_leave']" icon="calendar" color="sky" />
        <x-stat-tile label="Not Marked Yet" :value="$today['not_marked']" icon="eye-slash" color="rose"
            :hint="$today['on_notice'].' on notice period'" />
    </div>

    <x-dash-heading title="Waiting for approval" subtitle="Requests from employees" icon="inbox" tone="gold" />
    <div class="d-grid grid-cols-1 grid-cols-md-3 gap-4 mb-6">
        <x-stat-tile label="Leave Requests" :value="$pending['leaves']" icon="calendar" color="amber"
            :href="$pm::userCanAccessModule($u, 'leaves') ? route('admin.hr.leaves.index') : null" cta="Review" />
        <x-stat-tile label="Attendance Corrections" :value="$pending['regularizations']" icon="check" color="violet"
            :href="$pm::userCanAccessModule($u, 'attendance') ? route('admin.hr.regularizations.index') : null" cta="Review" />
        <x-stat-tile label="Expense Claims" :value="$pending['expenses']" icon="rupee" color="teal"
            :href="$pm::userCanAccessModule($u, 'expenses') ? route('admin.hr.expenses.index') : null" cta="Review" />
    </div>

    <div class="d-grid grid-cols-lg-2 gap-4 mb-6">
        <x-dash-card title="Headcount by department" subtitle="Current employees" icon="users">
            @php $max = max(1, $departments->max('employees_count')); @endphp
            @forelse ($departments as $d)
                <div class="mb-2">
                    <div class="d-flex justify-content-between small"><span>{{ $d->name }}</span><strong>{{ $d->employees_count }}</strong></div>
                    <div class="progress" style="height:6px"><div class="progress-bar bg-success" style="width: {{ $d->employees_count / $max * 100 }}%"></div></div>
                </div>
            @empty
                <p class="small text-secondary mb-0">
                    No departments yet.
                    @can('employees.edit') <a href="{{ route('admin.hr.organisation.index') }}">Add departments</a>. @endcan
                </p>
            @endforelse
        </x-dash-card>

        <x-dash-card title="Birthdays & work anniversaries" subtitle="Next 30 days" icon="sparkles">
            @forelse ($celebrations as $c)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2 small">
                    <div>
                        <strong>{{ $c['employee']->name }}</strong>
                        <span class="text-secondary">{{ $c['employee']->designation?->name }}</span>
                        <div class="fs-xs">
                            {{ $c['type'] === 'birthday' ? 'Birthday' : $c['years'].' year'.($c['years'] > 1 ? 's' : '').' with us' }}
                        </div>
                    </div>
                    <span class="badge text-bg-light border">{{ $c['date']->isSameDay(\App\Support\HrClock::today()) ? 'Today' : $c['date']->format('d M') }}</span>
                </div>
            @empty
                <p class="small text-secondary mb-0">Nothing in the next 30 days.</p>
            @endforelse
        </x-dash-card>

        <x-dash-card title="Recent joiners" icon="user">
            @forelse ($recentJoiners as $e)
                <div class="d-flex justify-content-between border-bottom py-2 small">
                    <span><strong>{{ $e->name }}</strong> <span class="text-secondary">{{ $e->designation?->name }}</span></span>
                    <span class="text-secondary">{{ $e->joining_date->format('d M Y') }}</span>
                </div>
            @empty
                <p class="small text-secondary mb-0">
                    No employees yet.
                    @can('employees.create') <a href="{{ route('admin.hr.employees.create') }}">Add the first one</a>. @endcan
                </p>
            @endforelse
        </x-dash-card>

        <x-dash-card title="Latest payroll" icon="rupee">
            @if ($latestPayroll)
                <p class="mb-1 fw-semibold">{{ $latestPayroll->label() }}
                    @include('hr.partials.badge', ['status' => $latestPayroll->status])
                </p>
                <p class="small text-secondary mb-2">{{ $latestPayroll->payslips_count }} payslips</p>
                <p class="h4 fw-bold mb-3">Rs. {{ number_format((float) $latestPayroll->payslips_sum_net_pay, 2) }}</p>
                @if ($pm::userCanAccessModule($u, 'payroll'))
                    <a href="{{ route('admin.hr.payroll.show', $latestPayroll) }}" class="btn btn-sm btn-outline-secondary">Open payroll</a>
                @endif
            @else
                <p class="small text-secondary mb-0">No payroll generated yet.</p>
            @endif
        </x-dash-card>
    </div>
</x-layouts.app>
