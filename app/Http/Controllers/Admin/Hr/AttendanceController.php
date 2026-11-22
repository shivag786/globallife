<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Attendance;
use App\Models\Hr\AttendanceRegularization;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\Hr\LeaveRequest;
use App\Services\Hr\AttendanceService;
use App\Services\Hr\WorkCalendarService;
use App\Support\HrAccess;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Attendance for HR: the day sheet (with manual marking), the monthly
 * report + CSV, and employees' correction requests.
 */
class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendance,
        private readonly WorkCalendarService $calendar,
    ) {}

    public function index(Request $request): View
    {
        HrAccess::module('attendance');

        $date = $this->parseDate($request->input('date'));

        $employees = Employee::working()->with(['user', 'department'])
            ->whereDate('joining_date', '<=', $date)
            ->when($request->filled('department'), fn ($q) => $q->where('department_id', $request->integer('department')))
            ->orderBy('employee_code')
            ->get();

        $records = Attendance::whereDate('date', $date)->whereIn('employee_id', $employees->pluck('id'))->get()->keyBy('employee_id');
        $leaves = LeaveRequest::with('leaveType')->where('status', 'approved')
            ->whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
            ->whereIn('employee_id', $employees->pluck('id'))->get()->keyBy('employee_id');

        return view('admin.hr.attendance.index', [
            'date' => $date,
            'employees' => $employees,
            'records' => $records,
            'leaves' => $leaves,
            'holiday' => $this->calendar->holidayOn($date),
            'departments' => Department::orderBy('name')->get(),
            'canEdit' => Auth::user()->can('attendance.edit'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = HrAccess::ability('attendance.edit');

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date', 'before_or_equal:'.\App\Support\HrClock::today()->toDateString()],
            'status' => ['nullable', Rule::in(array_keys(Attendance::STATUSES))],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $this->attendance->saveByAdmin($employee, $data, $actor);

        return back()->with('status', "Attendance saved for {$employee->name}.");
    }

    public function report(Request $request): View
    {
        HrAccess::module('attendance');

        [$month, $rows] = $this->monthlyRows($request);

        return view('admin.hr.attendance.report', [
            'month' => $month,
            'rows' => $rows,
            'departments' => Department::orderBy('name')->get(),
            'canExport' => Auth::user()->can('attendance.export'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        HrAccess::ability('attendance.export');

        [$month, $rows] = $this->monthlyRows($request);

        return response()->streamDownload(function () use ($rows, $month) {
            $out = fopen('php://output', 'w');
            $header = ['Employee ID', 'Name', 'Department', 'Present', 'Half Days', 'Absent', 'Paid Leave', 'Unpaid Leave', 'Holidays', 'Weekly Offs', 'Late', 'Paid Days'];
            foreach (range(1, $month->daysInMonth) as $d) {
                $header[] = (string) $d;
            }
            fputcsv($out, $header);

            $codes = ['present' => 'P', 'half_day' => 'HD', 'absent' => 'A', 'leave' => 'L', 'unpaid_leave' => 'LWP',
                'holiday' => 'H', 'weekly_off' => 'WO', 'na' => '-', 'upcoming' => ''];

            foreach ($rows as $row) {
                $s = $row['summary'];
                $line = [$row['employee']->employee_code, $row['employee']->name, $row['employee']->department?->name,
                    $s['present'], $s['half_day'], $s['absent'], $s['leave'], $s['unpaid_leave'], $s['holiday'], $s['weekly_off'], $s['late'], $s['paid']];
                foreach ($row['days'] as $day) {
                    $line[] = $codes[$day['code']] ?? '';
                }
                fputcsv($out, $line);
            }

            fclose($out);
        }, 'attendance-'.$month->format('Y-m').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function regularizations(Request $request): View
    {
        HrAccess::module('attendance');

        $status = $request->input('status', 'pending');

        return view('admin.hr.attendance.regularizations', [
            'status' => $status,
            'requests' => AttendanceRegularization::with(['employee.user', 'reviewer'])
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->latest()
                ->paginate(25)
                ->withQueryString(),
            'canApprove' => Auth::user()->can('attendance.approve'),
        ]);
    }

    public function approveRegularization(Request $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $actor = HrAccess::ability('attendance.approve');

        $this->attendance->approveRegularization($regularization, $actor, $request->input('review_note'));

        return back()->with('status', 'Correction approved and attendance updated.');
    }

    public function rejectRegularization(Request $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $actor = HrAccess::ability('attendance.approve');

        $request->validate(['review_note' => ['nullable', 'string', 'max:255']]);
        $this->attendance->rejectRegularization($regularization, $actor, $request->input('review_note'));

        return back()->with('status', 'Correction rejected.');
    }

    /**
     * @return array{0: Carbon, 1: list<array{employee: Employee, days: array<int, mixed>, summary: array<string, mixed>}>}
     */
    private function monthlyRows(Request $request): array
    {
        $month = $this->parseMonth($request->input('month'));
        $end = $month->copy()->endOfMonth();

        $employees = Employee::with(['user', 'department', 'shift'])
            ->whereDate('joining_date', '<=', $end)
            ->where(fn ($q) => $q->whereNull('exit_date')->orWhereDate('exit_date', '>=', $month))
            ->when($request->filled('department'), fn ($q) => $q->where('department_id', $request->integer('department')))
            ->orderBy('employee_code')
            ->get();

        $rows = [];
        foreach ($employees as $employee) {
            $days = $this->calendar->month($employee, $month);
            $rows[] = ['employee' => $employee, 'days' => $days, 'summary' => $this->calendar->summarize($days)];
        }

        return [$month, $rows];
    }

    private function parseDate(?string $value): Carbon
    {
        try {
            $date = $value ? Carbon::parse($value)->startOfDay() : \App\Support\HrClock::today();
        } catch (\Throwable) {
            $date = \App\Support\HrClock::today();
        }

        return $date->gt(\App\Support\HrClock::today()) ? \App\Support\HrClock::today() : $date;
    }

    private function parseMonth(?string $value): Carbon
    {
        try {
            return $value && preg_match('/^\d{4}-\d{2}$/', $value)
                ? Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfDay()
                : \App\Support\HrClock::now()->startOfMonth();
        } catch (\Throwable) {
            return \App\Support\HrClock::now()->startOfMonth();
        }
    }
}
