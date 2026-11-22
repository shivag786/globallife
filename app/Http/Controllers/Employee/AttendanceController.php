<?php

namespace App\Http\Controllers\Employee;

use App\Models\Hr\AttendanceRegularization;
use App\Services\Hr\AttendanceService;
use App\Services\Hr\WorkCalendarService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends EmployeePortalController
{
    public function __construct(private readonly AttendanceService $attendance) {}

    public function index(Request $request, WorkCalendarService $calendar): View
    {
        $employee = $this->employee();

        $month = \App\Support\HrClock::now()->startOfMonth();
        if (preg_match('/^\d{4}-\d{2}$/', (string) $request->input('month'))) {
            $month = Carbon::createFromFormat('Y-m-d', $request->input('month').'-01')->startOfDay();
        }
        if ($month->isAfter(\App\Support\HrClock::now())) {
            $month = \App\Support\HrClock::now()->startOfMonth();
        }

        $days = $calendar->month($employee, $month);

        return view('employee.attendance', [
            'employee' => $employee,
            'month' => $month,
            'days' => $days,
            'summary' => $calendar->summarize($days),
            'today' => $this->attendance->today($employee),
            'regularizations' => AttendanceRegularization::where('employee_id', $employee->id)->latest()->limit(10)->get(),
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $record = $this->attendance->checkIn($this->employee(), (string) $request->ip());

        return back()->with('status', 'Checked in at '.$record->check_in_at->format('h:i A').($record->is_late ? ' (late).' : '.'));
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $record = $this->attendance->checkOut($this->employee(), (string) $request->ip());

        return back()->with('status', 'Checked out at '.$record->check_out_at->format('h:i A').'. Worked '.$record->workedLabel().'.');
    }

    public function regularize(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:'.\App\Support\HrClock::today()->toDateString()],
            'check_in_time' => ['required', 'date_format:H:i'],
            'check_out_time' => ['required', 'date_format:H:i', 'after:check_in_time'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->attendance->requestRegularization($this->employee(), $data);

        return back()->with('status', 'Correction request sent to HR.');
    }
}
