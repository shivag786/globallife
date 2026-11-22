<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Department;
use App\Models\Hr\Designation;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Departments and designations, managed on one screen. */
class OrganisationController extends Controller
{
    public function index(): View
    {
        HrAccess::ability('employees.edit');

        return view('admin.hr.organisation.index', [
            'departments' => Department::withCount(['employees', 'designations'])->orderBy('name')->get(),
            'designations' => Designation::with('department')->withCount('employees')->orderBy('name')->get(),
        ]);
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        Department::create($request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('status', 'Department added.');
    }

    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $department->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Department updated.');
    }

    public function destroyDepartment(Department $department): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        if ($department->employees()->exists()) {
            return back()->with('error', 'This department still has employees. Move them first, or mark it inactive.');
        }

        $department->delete();

        return back()->with('status', 'Department deleted.');
    }

    public function storeDesignation(Request $request): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        Designation::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]));

        return back()->with('status', 'Designation added.');
    }

    public function updateDesignation(Request $request, Designation $designation): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $designation->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Designation updated.');
    }

    public function destroyDesignation(Designation $designation): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        if ($designation->employees()->exists()) {
            return back()->with('error', 'Employees still hold this designation. Change them first, or mark it inactive.');
        }

        $designation->delete();

        return back()->with('status', 'Designation deleted.');
    }
}
