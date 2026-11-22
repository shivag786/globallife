<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Holiday;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    public function index(Request $request): View
    {
        HrAccess::ability('employees.edit');

        $year = $request->integer('year') ?: (int) \App\Support\HrClock::now()->year;

        return view('admin.hr.holidays.index', [
            'year' => $year,
            'holidays' => Holiday::whereYear('date', $year)->orderBy('date')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        Holiday::create($request->validate([
            'date' => ['required', 'date', 'unique:holidays,date'],
            'name' => ['required', 'string', 'max:255'],
        ]));

        return back()->with('status', 'Holiday added.');
    }

    public function update(Request $request, Holiday $holiday): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $holiday->update($request->validate([
            'date' => ['required', 'date', Rule::unique('holidays', 'date')->ignore($holiday->id)],
            'name' => ['required', 'string', 'max:255'],
        ]));

        return back()->with('status', 'Holiday updated.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $holiday->delete();

        return back()->with('status', 'Holiday removed.');
    }
}
