<?php

namespace App\Http\Controllers\Employee;

use Illuminate\Contracts\View\View;

/** The employee's own HR record, read-only (changes go through HR). */
class ProfileController extends EmployeePortalController
{
    public function show(): View
    {
        return view('employee.profile', [
            'employee' => $this->employee()->load([
                'user', 'department', 'designation', 'shift', 'city', 'reportingManager.user', 'documents', 'salary',
                'assets', 'advances' => fn ($q) => $q->where('status', 'active'),
            ]),
        ]);
    }
}
