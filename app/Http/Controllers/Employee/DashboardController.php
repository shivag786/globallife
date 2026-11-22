<?php

namespace App\Http\Controllers\Employee;

use App\Models\Hr\Announcement;
use App\Models\Hr\Holiday;
use App\Models\Hr\LeaveRequest;
use App\Services\Hr\AttendanceService;
use App\Services\Hr\LeaveService;
use App\Services\Hr\WorkCalendarService;
use Illuminate\Contracts\View\View;

class DashboardController extends EmployeePortalController
{
    public function index(AttendanceService $attendance, LeaveService $leaves, WorkCalendarService $calendar): View
    {
        $employee = $this->employee()->load(['user', 'department', 'designation', 'shift', 'reportingManager.user']);

        $teamPending = $employee->directReports()->exists()
            ? LeaveRequest::where('status', 'pending')->whereIn('employee_id', $employee->directReports()->pluck('id'))->count()
            : 0;

        return view('employee.dashboard', [
            'employee' => $employee,
            'today' => $attendance->today($employee),
            'shift' => $employee->effectiveShift(),
            'isWorkingDay' => $calendar->isWorkingDay($employee, \App\Support\HrClock::today()),
            'holidayToday' => $calendar->holidayOn(\App\Support\HrClock::today()),
            'monthSummary' => $calendar->summarize($calendar->month($employee, \App\Support\HrClock::now()->startOfMonth())),
            'balances' => $leaves->balances($employee),
            'announcements' => Announcement::where('is_active', true)->latest()->limit(5)->get(),
            'holidays' => Holiday::whereDate('date', '>=', \App\Support\HrClock::today())->orderBy('date')->limit(5)->get(),
            'latestPayslip' => $employee->payslips()->whereHas('run', fn ($q) => $q->where('status', 'finalized'))
                ->with('run')->latest()->first(),
            'teamPending' => $teamPending,
            'pendingLeaves' => $employee->leaveRequests()->where('status', 'pending')->count(),
        ]);
    }
}
