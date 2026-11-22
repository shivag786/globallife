<x-layouts.app title="My Attendance" heading="My Attendance">
    @php
        $tone = ['present' => 'success', 'half_day' => 'warning', 'absent' => 'danger', 'leave' => 'info', 'unpaid_leave' => 'danger',
            'holiday' => 'primary', 'weekly_off' => 'secondary', 'na' => 'light', 'upcoming' => 'light'];
        $prev = $month->copy()->subMonth();
        $next = $month->copy()->addMonth();
    @endphp

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <a href="{{ route('employee.attendance.index', ['month' => $prev->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary">&larr;</a>
                    <strong>{{ $month->format('F Y') }}</strong>
                    @if ($next->lte(\App\Support\HrClock::now()))
                        <a href="{{ route('employee.attendance.index', ['month' => $next->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary">&rarr;</a>
                    @else
                        <span class="btn btn-sm btn-outline-secondary disabled">&rarr;</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="d-grid text-center fs-xs fw-semibold text-secondary mb-1" style="grid-template-columns: repeat(7, 1fr)">
                        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $d) <div>{{ $d }}</div> @endforeach
                    </div>
                    <div class="d-grid gap-1" style="grid-template-columns: repeat(7, 1fr)">
                        @for ($i = 0; $i < $month->dayOfWeek; $i++) <div></div> @endfor
                        @foreach ($days as $day)
                            @php $t = $tone[$day['code']] ?? 'light'; $a = $day['attendance']; @endphp
                            <div class="rounded border p-1 bg-{{ $t }}-subtle {{ $day['date']->isSameDay(\App\Support\HrClock::today()) ? 'border-2 border-dark' : '' }}" style="min-height: 4.2rem"
                                 title="{{ $day['label'] }}">
                                <div class="fs-xs fw-bold">{{ $day['date']->day }}</div>
                                <div class="text-{{ $t }}-emphasis" style="font-size: .68rem; line-height: 1.15">
                                    {{ $day['label'] }}
                                    @if ($a?->check_in_at)
                                        <div class="text-body-secondary">{{ $a->check_in_at->format('H:i') }}{{ $a->check_out_at ? '-'.$a->check_out_at->format('H:i') : '' }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer small d-flex flex-wrap gap-3">
                    <span>Present: <strong>{{ $summary['present'] }}</strong></span>
                    <span>Half days: <strong>{{ $summary['half_day'] }}</strong></span>
                    <span>Absent: <strong>{{ $summary['absent'] }}</strong></span>
                    <span>Leave: <strong>{{ $summary['leave'] + $summary['unpaid_leave'] }}</strong></span>
                    <span>Late: <strong>{{ $summary['late'] }}</strong></span>
                    <span>Paid days: <strong>{{ $summary['paid'] }}</strong></span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 d-flex flex-column gap-4">
            @include('employee.partials.punch-card')

            <x-dash-card title="Request a correction" subtitle="Forgot to check in or out?" icon="check">
                <form method="POST" action="{{ route('employee.attendance.regularize') }}" class="d-flex flex-column gap-2">
                    @csrf
                    <input type="date" name="date" max="{{ \App\Support\HrClock::today()->format('Y-m-d') }}" value="{{ old('date') }}" class="form-control form-control-sm @error('date') is-invalid @enderror" required>
                    @error('date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    <div class="d-flex gap-2">
                        <input type="time" name="check_in_time" value="{{ old('check_in_time') }}" class="form-control form-control-sm" title="Check-in" required>
                        <input type="time" name="check_out_time" value="{{ old('check_out_time') }}" class="form-control form-control-sm @error('check_out_time') is-invalid @enderror" title="Check-out" required>
                    </div>
                    <textarea name="reason" rows="2" class="form-control form-control-sm" placeholder="Reason" required>{{ old('reason') }}</textarea>
                    <button class="btn btn-sm btn-outline-secondary">Send to HR</button>
                </form>
                @if ($regularizations->isNotEmpty())
                    <div class="mt-3">
                        @foreach ($regularizations as $r)
                            <div class="d-flex justify-content-between small border-top py-1">
                                <span>{{ $r->date->format('d M') }} ({{ substr($r->check_in_time, 0, 5) }}-{{ substr($r->check_out_time, 0, 5) }})</span>
                                @include('hr.partials.badge', ['status' => $r->status])
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-dash-card>
        </div>
    </div>
</x-layouts.app>
