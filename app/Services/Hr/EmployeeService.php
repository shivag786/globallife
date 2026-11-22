<?php

namespace App\Services\Hr;

use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeDocument;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Creating and maintaining employees: the login (users row, role `employee`),
 * the HR record, the salary structure, photo and documents.
 *
 * HR files (photo, documents, receipts) live on the private `kyc` disk under
 * `hr/` and are only ever served through HrFileController.
 */
class EmployeeService
{
    public const DISK = 'kyc';

    public const DEFAULT_CODE_PREFIX = 'GL-EMP-';

    public function __construct(
        private readonly HrMailService $mail,
        private readonly SettingsService $settings,
    ) {}

    /**
     * @param  array{name: string, email: string, mobile?: ?string, password?: ?string}  $account
     * @param  array<string, mixed>  $hr
     * @param  array<string, mixed>|null  $salary
     * @return array{employee: Employee, password: string, emailed: bool}
     */
    public function create(array $account, array $hr, ?array $salary, ?UploadedFile $photo, User $actor): array
    {
        $password = ($account['password'] ?? null) ?: $this->generatePassword();

        $employee = DB::transaction(function () use ($account, $hr, $salary, $password, $actor) {
            $user = User::create([
                'name' => $account['name'],
                'email' => $account['email'],
                'mobile' => $account['mobile'] ?? null,
                'password' => Hash::make($password),
                'status' => 'active',
                'created_by' => $actor->id,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole('employee');

            $employee = Employee::create([
                ...$hr,
                'user_id' => $user->id,
                'employee_code' => $this->nextCode(),
                'employment_status' => 'active',
                'created_by' => $actor->id,
            ]);

            if ($salary !== null) {
                $employee->salary()->create($salary);
            }

            return $employee;
        });

        if ($photo) {
            $this->replacePhoto($employee, $photo);
        }

        // After commit: never email credentials for an account that rolled back.
        $emailed = $this->mail->credentials($employee->user, $employee->employee_code, $password);

        return ['employee' => $employee->fresh(['user']), 'password' => $password, 'emailed' => $emailed];
    }

    /**
     * @param  array{name: string, email: string, mobile?: ?string}  $account
     * @param  array<string, mixed>  $hr
     * @param  array<string, mixed>|null  $salary
     */
    public function update(Employee $employee, array $account, array $hr, ?array $salary, ?UploadedFile $photo): Employee
    {
        DB::transaction(function () use ($employee, $account, $hr, $salary) {
            $employee->user->update([
                'name' => $account['name'],
                'email' => $account['email'],
                'mobile' => $account['mobile'] ?? null,
            ]);

            $employee->update($hr);

            if ($salary !== null) {
                $employee->salary()->updateOrCreate(['employee_id' => $employee->id], $salary);
            }
        });

        if ($photo) {
            $this->replacePhoto($employee, $photo);
        }

        return $employee->fresh(['user']);
    }

    /**
     * Set a new password (typed by HR, or generated) and email it with the
     * Employee ID. Existing sessions are dropped, as with every other
     * password reset in the app.
     *
     * @return array{password: string, emailed: bool}
     */
    public function resetPassword(Employee $employee, ?string $password = null): array
    {
        $password = $password ?: $this->generatePassword();
        $user = $employee->user;

        DB::transaction(function () use ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            DB::table('sessions')->where('user_id', $user->id)->delete();
        });

        return [
            'password' => $password,
            'emailed' => $this->mail->credentials($user, $employee->employee_code, $password, true),
        ];
    }

    /**
     * Move an employee through their lifecycle. Leaving (resigned/terminated)
     * blocks the login; EnsureAccountIsActive then signs them out.
     */
    public function changeStatus(Employee $employee, string $status, ?string $exitDate = null, ?string $exitReason = null): Employee
    {
        DB::transaction(function () use ($employee, $status, $exitDate, $exitReason) {
            $working = in_array($status, Employee::WORKING_STATUSES, true);

            $employee->update([
                'employment_status' => $status,
                'exit_date' => $status === 'active' ? null : ($exitDate ?: ($working ? null : \App\Support\HrClock::now()->toDateString())),
                'exit_reason' => $status === 'active' ? null : $exitReason,
            ]);

            $employee->user->update(['status' => $working ? 'active' : 'blocked']);
        });

        return $employee;
    }

    public function addDocument(Employee $employee, string $title, UploadedFile $file, User $actor): EmployeeDocument
    {
        $path = $file->store("hr/{$employee->id}/documents", self::DISK);

        return $employee->documents()->create([
            'title' => $title,
            'path' => $path,
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $actor->id,
        ]);
    }

    public function deleteDocument(EmployeeDocument $document): void
    {
        Storage::disk(self::DISK)->delete($document->path);
        $document->delete();
    }

    public function replacePhoto(Employee $employee, UploadedFile $photo): void
    {
        $old = $employee->photo_path;
        $employee->update(['photo_path' => $photo->store("hr/{$employee->id}/photo", self::DISK)]);

        if ($old) {
            Storage::disk(self::DISK)->delete($old);
        }
    }

    public function codePrefix(): string
    {
        return $this->settings->get('hr_employee_code_prefix') ?: self::DEFAULT_CODE_PREFIX;
    }

    /**
     * Next sequential code, e.g. GL-EMP-0007. Runs inside the create
     * transaction; the lock keeps two simultaneous creates from taking the
     * same number (the unique index is the final guard).
     */
    private function nextCode(): string
    {
        $prefix = $this->codePrefix();

        $last = Employee::where('employee_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->pluck('employee_code')
            ->map(fn (string $code) => (int) substr($code, strlen($prefix)))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    /** Easy to read out or type: no symbols, no look-alike characters. */
    private function generatePassword(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $password = '';

        for ($i = 0; $i < 10; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }
}
