<?php

namespace App\Http\Controllers\Employee;

use App\Models\Hr\Holiday;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Services\Hr\LeaveService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The employee's own leave, and — for anyone with direct reports — their
 * team's requests to approve.
 */
class LeaveController extends EmployeePortalController
{
    public function __construct(private readonly LeaveService $leaves) {}

    public function index(): View
    {
        $employee = $this->employee();

        return view('employee.leaves', [
            'employee' => $employee,
            'balances' => $this->leaves->balances($employee),
            'types' => LeaveType::where('is_active', true)->orderBy('name')->get(),
            'requests' => $employee->leaveRequests()->with(['leaveType', 'reviewer'])->latest()->paginate(15),
            'holidays' => Holiday::whereYear('date', \App\Support\HrClock::now()->year)->orderBy('date')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'is_half_day' => ['nullable', 'boolean'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $leave = $this->leaves->apply($this->employee(), $data);

        return back()->with('status', "Leave request for {$leave->days} day(s) submitted for approval.");
    }

    public function cancel(LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless($leaveRequest->employee_id === $this->employee()->id, 404);

        $this->leaves->cancel($leaveRequest);

        return back()->with('status', 'Leave request cancelled.');
    }

    public function team(): View
    {
        $employee = $this->employee();
        $teamIds = $employee->directReports()->pluck('id');

        abort_if($teamIds->isEmpty(), 403, 'Nobody reports to you.');

        return view('employee.team-leaves', [
            'pending' => LeaveRequest::with(['employee.user', 'leaveType'])->whereIn('employee_id', $teamIds)
                ->where('status', 'pending')->orderBy('from_date')->get(),
            'recent' => LeaveRequest::with(['employee.user', 'leaveType', 'reviewer'])->whereIn('employee_id', $teamIds)
                ->where('status', '!=', 'pending')->latest('updated_at')->limit(20)->get(),
            'team' => $employee->directReports()->with(['user', 'designation'])->working()->get(),
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorizeReview($leaveRequest);
        $request->validate(['review_note' => ['nullable', 'string', 'max:255']]);

        $this->leaves->approve($leaveRequest, Auth::user(), $request->input('review_note'));

        return back()->with('status', 'Leave approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorizeReview($leaveRequest);
        $request->validate(['review_note' => ['nullable', 'string', 'max:255']]);

        $this->leaves->reject($leaveRequest, Auth::user(), $request->input('review_note'));

        return back()->with('status', 'Leave rejected.');
    }

    private function authorizeReview(LeaveRequest $leaveRequest): void
    {
        $this->employee();

        abort_unless($this->leaves->canReview(Auth::user(), $leaveRequest), 403);
    }
}
