<?php

namespace App\Repositories;

use App\Models\Hr\Attendance;
use App\Models\Hr\AttendanceRegularization;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\Hr\ExpenseClaim;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\PayrollRun;
use Illuminate\Support\Collection;

/** Read-only numbers for the HR dashboard. */
class HrDashboardRepository
{
    /**
     * @return array{total: int, present: int, late: int, on_leave: int, not_marked: int, on_notice: int}
     */
    public function today(): array
    {
        $working = Employee::working()->whereDate('joining_date', '<=', \App\Support\HrClock::today());
        $total = (clone $working)->count();
        $ids = (clone $working)->pluck('id');

        $attendance = Attendance::whereDate('date', \App\Support\HrClock::today())->whereIn('employee_id', $ids)->get();
        $present = $attendance->whereIn('status', ['present', 'half_day'])->count();

        $onLeave = LeaveRequest::where('status', 'approved')
            ->whereIn('employee_id', $ids)
            ->whereDate('from_date', '<=', \App\Support\HrClock::today())
            ->whereDate('to_date', '>=', \App\Support\HrClock::today())
            ->distinct('employee_id')
            ->count('employee_id');

        return [
            'total' => $total,
            'present' => $present,
            'late' => $attendance->where('is_late', true)->count(),
            'on_leave' => $onLeave,
            'not_marked' => max(0, $total - $attendance->count() - $onLeave),
            'on_notice' => Employee::where('employment_status', 'on_notice')->count(),
        ];
    }

    /**
     * @return array{leaves: int, regularizations: int, expenses: int}
     */
    public function pending(): array
    {
        return [
            'leaves' => LeaveRequest::where('status', 'pending')->count(),
            'regularizations' => AttendanceRegularization::where('status', 'pending')->count(),
            'expenses' => ExpenseClaim::where('status', 'pending')->count(),
        ];
    }

    /** @return Collection<int, Department> */
    public function departmentHeadcount(): Collection
    {
        return Department::withCount(['employees' => fn ($q) => $q->working()])
            ->orderByDesc('employees_count')
            ->get();
    }

    /**
     * Birthdays and work anniversaries in the next `$days` days.
     *
     * @return Collection<int, array{employee: Employee, type: string, date: \Carbon\Carbon, years: int|null}>
     */
    public function upcomingCelebrations(int $days = 30): Collection
    {
        $today = \App\Support\HrClock::today();
        $events = collect();

        foreach (Employee::working()->with(['user', 'designation'])->get() as $employee) {
            foreach (['birthday' => $employee->date_of_birth, 'anniversary' => $employee->joining_date] as $type => $date) {
                if (! $date) {
                    continue;
                }

                $next = $date->copy()->year($today->year);
                if ($next->lt($today)) {
                    $next->addYear();
                }

                $years = $next->year - $date->year;
                if ($next->diffInDays($today, true) <= $days && ($type === 'birthday' || $years > 0)) {
                    $events->push(['employee' => $employee, 'type' => $type, 'date' => $next, 'years' => $type === 'anniversary' ? $years : null]);
                }
            }
        }

        return $events->sortBy('date')->values();
    }

    /** @return Collection<int, Employee> */
    public function recentJoiners(int $limit = 5): Collection
    {
        return Employee::with(['user', 'designation'])->latest('joining_date')->limit($limit)->get();
    }

    public function latestPayroll(): ?PayrollRun
    {
        return PayrollRun::withSum('payslips', 'net_pay')->withCount('payslips')->latest('month')->first();
    }
}
