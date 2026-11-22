<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Shift;
use App\Services\Hr\AttendanceService;
use App\Services\Hr\EmployeeService;
use App\Services\SettingsService;
use App\Support\HrAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Shifts, plus the HR settings (office IP restriction, Employee ID prefix). */
class ShiftController extends Controller
{
    public function index(SettingsService $settings, EmployeeService $employees): View
    {
        HrAccess::ability('employees.edit');

        return view('admin.hr.shifts.index', [
            'shifts' => Shift::withCount('employees')->orderBy('start_time')->get(),
            'allowedIps' => $settings->get(AttendanceService::IP_SETTING),
            'codePrefix' => $employees->codePrefix(),
            'currentIp' => request()->ip(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $this->save(new Shift, $this->validated($request));

        return back()->with('status', 'Shift added.');
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $this->save($shift, $this->validated($request));

        return back()->with('status', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        if ($shift->is_default) {
            return back()->with('error', 'The default shift cannot be deleted. Make another shift the default first.');
        }

        // Its employees fall back to the default shift (shift_id is nulled).
        $shift->delete();

        return back()->with('status', 'Shift deleted.');
    }

    public function updateSettings(Request $request, SettingsService $settings): RedirectResponse
    {
        HrAccess::ability('employees.edit');

        $data = $request->validate([
            'allowed_ips' => ['nullable', 'string', 'max:1000'],
            'code_prefix' => ['required', 'string', 'max:15', 'regex:/^[A-Za-z0-9-]+$/'],
        ]);

        $ips = collect(explode(',', (string) ($data['allowed_ips'] ?? '')))->map(fn ($ip) => trim($ip))->filter();
        $invalid = $ips->reject(fn ($ip) => filter_var($ip, FILTER_VALIDATE_IP));

        if ($invalid->isNotEmpty()) {
            return back()->withErrors(['allowed_ips' => 'Not a valid IP address: '.$invalid->implode(', ')])->withInput();
        }

        $settings->set(AttendanceService::IP_SETTING, $ips->implode(','));
        $settings->set('hr_employee_code_prefix', strtoupper($data['code_prefix']));

        return back()->with('status', 'HR settings saved.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'half_day_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'full_day_minutes' => ['required', 'integer', 'min:1', 'max:1440', 'gte:half_day_minutes'],
            'weekly_offs' => ['nullable', 'array'],
            'weekly_offs.*' => ['integer', 'between:0,6'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function save(Shift $shift, array $data): void
    {
        DB::transaction(function () use ($shift, $data) {
            $makeDefault = (bool) ($data['is_default'] ?? false) || ! Shift::where('is_default', true)->whereKeyNot($shift->id ?? 0)->exists();

            if ($makeDefault) {
                Shift::where('is_default', true)->update(['is_default' => false]);
            }

            $shift->fill([
                ...$data,
                'weekly_offs' => array_map('intval', $data['weekly_offs'] ?? []),
                'is_default' => $makeDefault,
            ])->save();
        });
    }
}
