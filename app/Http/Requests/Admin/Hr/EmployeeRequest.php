<?php

namespace App\Http\Requests\Admin\Hr;

use App\Models\Hr\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create / edit an employee. Authorisation is done in the controller
 * (HrAccess), so this only validates.
 */
class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'pan_number' => $this->filled('pan_number') ? strtoupper(trim((string) $this->input('pan_number'))) : null,
            'bank_ifsc' => $this->filled('bank_ifsc') ? strtoupper(trim((string) $this->input('bank_ifsc'))) : null,
            'aadhaar_number' => $this->filled('aadhaar_number') ? preg_replace('/\D/', '', (string) $this->input('aadhaar_number')) : null,
            'mobile' => $this->filled('mobile') ? preg_replace('/^(\+91|0)/', '', preg_replace('/[\s-]/', '', (string) $this->input('mobile'))) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Employee|null $employee */
        $employee = $this->route('employee');
        $money = ['nullable', 'numeric', 'min:0', 'max:99999999'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee?->user_id)],
            'mobile' => ['nullable', 'digits:10'],
            'password' => $employee ? ['prohibited'] : ['nullable', 'string', 'min:8', 'max:64'],

            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'reporting_manager_id' => ['nullable', 'exists:employees,id', Rule::notIn(array_filter([$employee?->id]))],
            'city_id' => ['nullable', 'exists:cities,id'],
            'employment_type' => ['required', Rule::in(array_keys(Employee::EMPLOYMENT_TYPES))],
            'joining_date' => ['required', 'date'],
            'probation_end_date' => ['nullable', 'date', 'after_or_equal:joining_date'],
            'confirmation_date' => ['nullable', 'date', 'after_or_equal:joining_date'],

            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'blood_group' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'other'])],
            'current_address' => ['nullable', 'string', 'max:1000'],
            'permanent_address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],

            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_ifsc' => ['nullable', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],
            'upi_id' => ['nullable', 'string', 'max:100'],
            'pan_number' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'aadhaar_number' => ['nullable', 'digits:12'],
            'uan_number' => ['nullable', 'string', 'max:20'],
            'esi_number' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],

            'salary.basic' => $money,
            'salary.hra' => $money,
            'salary.conveyance' => $money,
            'salary.special_allowance' => $money,
            'salary.other_allowance' => $money,
            'salary.professional_tax' => $money,
            'salary.tds' => $money,
            'salary.pf_enabled' => ['nullable', 'boolean'],
            'salary.esi_enabled' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pan_number.regex' => 'Enter a valid PAN, e.g. ABCDE1234F.',
            'bank_ifsc.regex' => 'Enter a valid IFSC, e.g. SBIN0001234.',
            'reporting_manager_id.not_in' => 'An employee cannot report to themselves.',
        ];
    }

    /** @return array{name: string, email: string, mobile: ?string, password: ?string} */
    public function account(): array
    {
        return [
            'name' => $this->validated('name'),
            'email' => $this->validated('email'),
            'mobile' => $this->validated('mobile'),
            'password' => $this->validated('password'),
        ];
    }

    /** @return array<string, mixed> */
    public function hr(): array
    {
        return collect($this->validated())
            ->except(['name', 'email', 'mobile', 'password', 'photo', 'salary'])
            ->all();
    }

    /**
     * The salary structure, or null when no basic pay was entered (an
     * employee without salary simply stays out of payroll).
     *
     * @return array<string, mixed>|null
     */
    public function salary(): ?array
    {
        $salary = $this->validated('salary') ?? [];

        if (! isset($salary['basic']) || $salary['basic'] === '' || $salary['basic'] === null) {
            return null;
        }

        return [
            'basic' => (float) $salary['basic'],
            'hra' => (float) ($salary['hra'] ?? 0),
            'conveyance' => (float) ($salary['conveyance'] ?? 0),
            'special_allowance' => (float) ($salary['special_allowance'] ?? 0),
            'other_allowance' => (float) ($salary['other_allowance'] ?? 0),
            'professional_tax' => (float) ($salary['professional_tax'] ?? 0),
            'tds' => (float) ($salary['tds'] ?? 0),
            'pf_enabled' => (bool) ($salary['pf_enabled'] ?? false),
            'esi_enabled' => (bool) ($salary['esi_enabled'] ?? false),
        ];
    }
}
