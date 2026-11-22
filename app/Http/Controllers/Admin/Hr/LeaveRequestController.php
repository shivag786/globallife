<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Services\Hr\LeaveService;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveService $leaves) {}

    public function index(Request $request): View
    {
        HrAccess::module('leaves');

        $status = $request->input('status', 'pending');

        return view('admin.hr.leaves.index', [
            'status' => $status,
            'types' => LeaveType::orderBy('name')->get(),
            'requests' => LeaveRequest::with(['employee.user', 'employee.department', 'leaveType', 'reviewer'])
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->when($request->filled('type'), fn ($q) => $q->where('leave_type_id', $request->integer('type')))
                ->when($request->filled('q'), fn ($q) => $q->whereHas('employee', fn ($e) => $e
                    ->where('employee_code', 'like', '%'.$request->input('q').'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$request->input('q').'%'))))
                ->orderByRaw("status = 'pending' desc")
                ->latest()
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $actor = HrAccess::ability('leaves.approve');

        $request->validate(['review_note' => ['nullable', 'string', 'max:255']]);
        $this->leaves->approve($leaveRequest, $actor, $request->input('review_note'));

        return back()->with('status', 'Leave approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $actor = HrAccess::ability('leaves.approve');

        $request->validate(['review_note' => ['nullable', 'string', 'max:255']]);
        $this->leaves->reject($leaveRequest, $actor, $request->input('review_note'));

        return back()->with('status', 'Leave rejected.');
    }
}
