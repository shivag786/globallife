<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hr\EmployeeRequest;
use App\Models\City;
use App\Models\Hr\Department;
use App\Models\Hr\Designation;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\Shift;
use App\Services\Hr\EmployeeService;
use App\Services\Hr\LeaveService;
use App\Services\Hr\WorkCalendarService;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employees) {}

    public function index(Request $request): View
    {
        HrAccess::module('employees');

        $employees = Employee::with(['user', 'department', 'designation'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->input('q').'%';
                $q->where(fn ($w) => $w->where('employee_code', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('mobile', 'like', $term)));
            })
            ->when($request->filled('department'), fn ($q) => $q->where('department_id', $request->integer('department')))
            ->when(
                $request->filled('status'),
                fn ($q) => $request->input('status') === 'all' ? $q : $q->where('employment_status', $request->input('status')),
                fn ($q) => $q->working(),
            )
            ->orderBy('employee_code')
            ->paginate(25)
            ->withQueryString();

        return view('admin.hr.employees.index', [
            'employees' => $employees,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        HrAccess::ability('employees.create');

        return view('admin.hr.employees.create', $this->formData(null) + ['nextCodePrefix' => $this->employees->codePrefix()]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $actor = HrAccess::ability('employees.create');

        $result = $this->employees->create($request->account(), $request->hr(), $request->salary(), $request->file('photo'), $actor);
        $employee = $result['employee'];

        $message = "Employee {$employee->name} created with ID {$employee->employee_code}. ";
        $message .= $result['emailed']
            ? "Login details have been emailed to {$employee->user->email}."
            : 'The email could not be sent - please share the password shown below with them.';

        return redirect()->route('admin.hr.employees.show', $employee)
            ->with('status', $message)
            ->with('credentials', $result['emailed'] ? null : ['code' => $employee->employee_code, 'password' => $result['password']]);
    }

    public function show(Employee $employee, LeaveService $leaves, WorkCalendarService $calendar): View
    {
        HrAccess::module('employees');

        $employee->load(['user', 'department', 'designation', 'shift', 'reportingManager.user', 'city', 'salary', 'documents',
            'assets', 'advances' => fn ($q) => $q->latest('issued_on'), 'directReports.user']);

        $month = $calendar->month($employee, \App\Support\HrClock::now()->startOfMonth());

        return view('admin.hr.employees.show', [
            'employee' => $employee,
            'balances' => $leaves->balances($employee),
            'monthSummary' => $calendar->summarize($month),
            'recentLeaves' => $employee->leaveRequests()->with('leaveType')->latest()->limit(5)->get(),
            'payslips' => $employee->payslips()->with('run')->latest()->limit(6)->get(),
        ]);
    }

    public function edit(Employee $employee): View
    {
        HrAccess::ability('employees.edit');

        return view('admin.hr.employees.edit', $this->formData($employee->load(['user', 'salary'])));
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $this->employees->update($employee, $request->account(), $request->hr(), $request->salary(), $request->file('photo'));

        return redirect()->route('admin.hr.employees.show', $employee)->with('status', 'Employee details updated.');
    }

    /**
     * New password, emailed with the Employee ID. HR may type one or leave it
     * blank to generate one.
     */
    public function resetPassword(Request $request, Employee $employee): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $data = $request->validate(['password' => ['nullable', 'string', 'min:8', 'max:64']]);
        $result = $this->employees->resetPassword($employee, $data['password'] ?? null);

        return back()
            ->with('status', $result['emailed']
                ? "New password emailed to {$employee->user->email}. They have been signed out everywhere."
                : 'Password changed, but the email could not be sent - share the password shown below.')
            ->with('credentials', $result['emailed'] ? null : ['code' => $employee->employee_code, 'password' => $result['password']]);
    }

    public function updateStatus(Request $request, Employee $employee): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $data = $request->validate([
            'employment_status' => ['required', Rule::in(array_keys(Employee::STATUSES))],
            'exit_date' => ['nullable', 'date', 'after_or_equal:'.$employee->joining_date->toDateString()],
            'exit_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->employees->changeStatus($employee, $data['employment_status'], $data['exit_date'] ?? null, $data['exit_reason'] ?? null);

        return back()->with('status', "Status changed to {$employee->statusLabel()}.");
    }

    public function storeDocument(Request $request, Employee $employee): RedirectResponse
    {
        $actor = HrAccess::ability('employees.edit');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:5120'],
        ]);

        $this->employees->addDocument($employee, $data['title'], $request->file('document'), $actor);

        return back()->with('status', 'Document uploaded.');
    }

    public function destroyDocument(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        HrAccess::ability('employees.edit');
        abort_unless($document->employee_id === $employee->id, 404);

        $this->employees->deleteDocument($document);

        return back()->with('status', 'Document deleted.');
    }

    public function export(): StreamedResponse
    {
        HrAccess::ability('employees.export');

        $employees = Employee::with(['user', 'department', 'designation', 'reportingManager.user', 'salary'])->orderBy('employee_code')->get();

        return response()->streamDownload(function () use ($employees) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee ID', 'Name', 'Email', 'Mobile', 'Department', 'Designation', 'Reporting To', 'Type',
                'Joining Date', 'Date of Birth', 'Status', 'Exit Date', 'Monthly Gross']);

            foreach ($employees as $e) {
                fputcsv($out, [
                    $e->employee_code, $e->name, $e->user?->email, $e->user?->mobile, $e->department?->name, $e->designation?->name,
                    $e->reportingManager?->name, $e->typeLabel(), $e->joining_date?->format('Y-m-d'), $e->date_of_birth?->format('Y-m-d'),
                    $e->statusLabel(), $e->exit_date?->format('Y-m-d'), $e->salary?->gross(),
                ]);
            }

            fclose($out);
        }, 'employees-'.\App\Support\HrClock::now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /** @return array<string, mixed> */
    private function formData(?Employee $employee): array
    {
        return [
            'employee' => $employee,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'designations' => Designation::with('department')->where('is_active', true)->orderBy('name')->get(),
            'shifts' => Shift::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
            'managers' => Employee::working()->with('user')
                ->when($employee, fn ($q) => $q->whereKeyNot($employee->id))
                ->get()->sortBy('name'),
            'canSeeSalary' => Auth::user()->can('payroll.create') || Auth::user()->can('employees.edit'),
        ];
    }
}
