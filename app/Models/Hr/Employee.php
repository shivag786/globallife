<?php

namespace App\Models\Hr;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * The HR record of a staff member. Their login is the linked `users` row
 * (role `employee`); name, email and mobile live there.
 */
#[Fillable([
    'user_id', 'employee_code', 'department_id', 'designation_id', 'shift_id', 'reporting_manager_id', 'city_id',
    'employment_type', 'joining_date', 'probation_end_date', 'confirmation_date',
    'date_of_birth', 'gender', 'blood_group', 'marital_status', 'current_address', 'permanent_address',
    'emergency_contact_name', 'emergency_contact_relation', 'emergency_contact_phone',
    'bank_name', 'bank_account_name', 'bank_account_number', 'bank_ifsc', 'upi_id',
    'pan_number', 'aadhaar_number', 'uan_number', 'esi_number', 'photo_path',
    'employment_status', 'exit_date', 'exit_reason', 'created_by',
])]
class Employee extends Model
{
    use LogsActivity;

    public const EMPLOYMENT_TYPES = [
        'full_time' => 'Full-time',
        'part_time' => 'Part-time',
        'contract' => 'Contract',
        'intern' => 'Intern',
    ];

    /** Statuses in which the employee still works here (and can log in). */
    public const WORKING_STATUSES = ['active', 'on_notice'];

    public const STATUSES = [
        'active' => 'Active',
        'on_notice' => 'On Notice',
        'resigned' => 'Resigned',
        'terminated' => 'Terminated',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'probation_end_date' => 'date',
            'confirmation_date' => 'date',
            'date_of_birth' => 'date',
            'exit_date' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['department_id', 'designation_id', 'shift_id', 'reporting_manager_id', 'employment_type', 'employment_status', 'exit_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @param Builder<Employee> $query */
    public function scopeWorking(Builder $query): Builder
    {
        return $query->whereIn('employment_status', self::WORKING_STATUSES);
    }

    public function isWorking(): bool
    {
        return in_array($this->employment_status, self::WORKING_STATUSES, true);
    }

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? '';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->employment_status] ?? ucfirst((string) $this->employment_status);
    }

    public function typeLabel(): string
    {
        return self::EMPLOYMENT_TYPES[$this->employment_type] ?? (string) $this->employment_type;
    }

    public function effectiveShift(): ?Shift
    {
        return $this->shift ?? Shift::default();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<Designation, $this> */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /** @return BelongsTo<Shift, $this> */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    /** @return HasMany<Employee, $this> */
    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'reporting_manager_id');
    }

    /** @return HasOne<EmployeeSalary, $this> */
    public function salary(): HasOne
    {
        return $this->hasOne(EmployeeSalary::class);
    }

    /** @return HasMany<EmployeeDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class)->latest();
    }

    /** @return HasMany<Attendance, $this> */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** @return HasMany<LeaveRequest, $this> */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /** @return HasMany<LeaveBalance, $this> */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /** @return HasMany<Payslip, $this> */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    /** @return HasMany<SalaryAdvance, $this> */
    public function advances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class);
    }

    /** @return HasMany<Asset, $this> */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /** @return HasMany<ExpenseClaim, $this> */
    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class);
    }
}
