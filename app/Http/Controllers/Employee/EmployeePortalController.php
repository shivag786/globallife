<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use Illuminate\Support\Facades\Auth;

/** Base for the employee portal: every page works on the signed-in employee. */
abstract class EmployeePortalController extends Controller
{
    protected function employee(): Employee
    {
        $employee = Auth::user()->employee;

        abort_unless($employee !== null, 403, 'No employee record is linked to this account. Please contact HR.');

        return $employee;
    }
}
