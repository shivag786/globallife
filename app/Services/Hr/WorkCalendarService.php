<?php

namespace App\Services\Hr;

use App\Models\Hr\Attendance;
use App\Models\Hr\Employee;
use App\Models\Hr\Holiday;
use App\Models\Hr\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

/**
 * What each day is for one employee: worked, half day, on leave, holiday,
 * weekly off, absent, or outside their employment. Attendance reports, the
 * employee's own calendar, and payroll all read days through here, so a day
 * is never counted two different ways.
 *
 * `paid` is the fraction of the day that is paid: 1, 0.5 or 0 (null for
 * days that do not count at all, before joining / after leaving / in the
 * future).
 */
class WorkCalendarService
{
    /** @var array<string, string>|null */
    private ?array $holidayCache = null;

    /**
     * Holidays keyed by Y-m-d. Loaded once per request.
     *
     * @return array<string, string>
     */
    public function holidays(): array
    {
        return $this->holidayCache ??= Holiday::orderBy('date')->get()
            ->mapWithKeys(fn (Holiday $h) => [$h->date->toDateString() => $h->name])
            ->all();
    }

    public function holidayOn(CarbonInterface $date): ?string
    {
        return $this->holidays()[$date->toDateString()] ?? null;
    }

    /** Not a weekly off of the employee's shift, and not a company holiday. */
    public function isWorkingDay(Employee $employee, CarbonInterface $date): bool
    {
        $shift = $employee->effectiveShift();

        return ! ($shift?->isWeeklyOff($date) ?? $date->isSunday()) && ! $this->holidayOn($date);
    }

    /**
     * Every day of the month for one employee.
     *
     * @return list<array{date: Carbon, code: string, label: string, paid: float|null, attendance: Attendance|null, leave: LeaveRequest|null}>
     */
    public function month(Employee $employee, CarbonInterface $monthStart): array
    {
        $from = Carbon::parse($monthStart)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $attendances = $employee->attendances()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $a) => $a->date->toDateString());

        $leaves = $employee->leaveRequests()
            ->with('leaveType')
            ->where('status', 'approved')
            ->where('from_date', '<=', $to->toDateString())
            ->where('to_date', '>=', $from->toDateString())
            ->get();

        $days = [];
        foreach (CarbonPeriod::create($from, $to) as $date) {
            $days[] = $this->classify($employee, $date, $attendances->get($date->toDateString()), $leaves);
        }

        return $days;
    }

    /**
     * Totals of a month() result.
     *
     * @param  list<array{code: string, paid: float|null}>  $days
     * @return array{counted: int, paid: float, present: float, half_day: int, absent: int, leave: float, unpaid_leave: float, holiday: int, weekly_off: int, late: int}
     */
    public function summarize(array $days): array
    {
        $s = ['counted' => 0, 'paid' => 0.0, 'present' => 0.0, 'half_day' => 0, 'absent' => 0, 'leave' => 0.0,
            'unpaid_leave' => 0.0, 'holiday' => 0, 'weekly_off' => 0, 'late' => 0];

        foreach ($days as $day) {
            if ($day['paid'] === null) {
                continue;
            }

            $s['counted']++;
            $s['paid'] += $day['paid'];

            match ($day['code']) {
                'present' => $s['present']++,
                'half_day' => [$s['half_day']++, $s['present'] += 0.5],
                'absent' => $s['absent']++,
                'leave' => $s['leave'] += ($day['leave']?->is_half_day ? 0.5 : 1),
                'unpaid_leave' => $s['unpaid_leave'] += ($day['leave']?->is_half_day ? 0.5 : 1),
                'holiday' => $s['holiday']++,
                'weekly_off' => $s['weekly_off']++,
                default => null,
            };

            if (($day['attendance'] ?? null)?->is_late) {
                $s['late']++;
            }
        }

        return $s;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, LeaveRequest>  $leaves
     * @return array{date: Carbon, code: string, label: string, paid: float|null, attendance: Attendance|null, leave: LeaveRequest|null}
     */
    private function classify(Employee $employee, Carbon $date, ?Attendance $attendance, $leaves): array
    {
        $day = fn (string $code, string $label, ?float $paid, ?LeaveRequest $leave = null) => [
            'date' => $date->copy(), 'code' => $code, 'label' => $label, 'paid' => $paid,
            'attendance' => $attendance, 'leave' => $leave,
        ];

        if ($date->lt($employee->joining_date) || ($employee->exit_date && $date->gt($employee->exit_date))) {
            return $day('na', '-', null);
        }

        $leave = $leaves->first(fn (LeaveRequest $l) => $date->betweenIncluded($l->from_date, $l->to_date));
        $paidLeave = $leave && $leave->leaveType?->is_paid;

        // Worked days first: someone who worked on a holiday was present.
        if ($attendance && $attendance->status === 'present') {
            return $day('present', $attendance->is_late ? 'Present (late)' : 'Present', 1.0);
        }

        if ($attendance && $attendance->status === 'half_day') {
            // The other half is paid only if it was covered by a paid half-day leave.
            $paid = 0.5 + ($leave && $leave->is_half_day && $paidLeave ? 0.5 : 0);

            return $day('half_day', 'Half Day', $paid, $leave);
        }

        if ($holiday = $this->holidayOn($date)) {
            return $day('holiday', $holiday, 1.0);
        }

        if (! $this->isWorkingDay($employee, $date)) {
            return $day('weekly_off', 'Weekly Off', 1.0);
        }

        if ($leave) {
            $fraction = $leave->is_half_day ? 0.5 : 1.0;

            return $paidLeave
                ? $day('leave', $leave->leaveType->code.' Leave', $fraction, $leave)
                : $day('unpaid_leave', 'Unpaid Leave', 0.0, $leave);
        }

        if ($date->isAfter(\App\Support\HrClock::today())) {
            return $day('upcoming', '', null);
        }

        // Today with no check-in yet is not an absence (yet).
        if ($date->isSameDay(\App\Support\HrClock::today()) && ! $attendance) {
            return $day('upcoming', 'Not marked', null);
        }

        return $day('absent', 'Absent', 0.0);
    }
}
