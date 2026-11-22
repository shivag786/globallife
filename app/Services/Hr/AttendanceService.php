<?php

namespace App\Services\Hr;

use App\Models\Hr\Attendance;
use App\Models\Hr\AttendanceRegularization;
use App\Models\Hr\Employee;
use App\Models\Hr\Shift;
use App\Models\User;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Daily attendance: self check-in / check-out from the employee portal, HR
 * corrections, and regularization requests.
 *
 * Status rules come from the employee's shift:
 *  - checked in after start + grace minutes  → marked late
 *  - worked ≥ full_day_minutes               → present
 *  - worked ≥ half_day_minutes               → half day
 *  - less                                    → absent
 */
class AttendanceService
{
    public const IP_SETTING = 'hr_attendance_ips';

    public function __construct(
        private readonly SettingsService $settings,
        private readonly HrMailService $mail,
    ) {}

    public function today(Employee $employee): ?Attendance
    {
        return $employee->attendances()->whereDate('date', \App\Support\HrClock::today())->first();
    }

    public function checkIn(Employee $employee, string $ip): Attendance
    {
        $this->assertAllowedIp($ip);

        if ($this->today($employee)?->check_in_at) {
            throw ValidationException::withMessages(['attendance' => 'You have already checked in today.']);
        }

        $now = \App\Support\HrClock::now();
        $shift = $employee->effectiveShift();

        return Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $now->toDateString()],
            [
                'check_in_at' => $now,
                'check_in_ip' => $ip,
                'status' => 'present',
                'is_late' => $shift ? $this->isLate($shift, $now) : false,
                'source' => 'self',
            ],
        );
    }

    public function checkOut(Employee $employee, string $ip): Attendance
    {
        $this->assertAllowedIp($ip);

        $attendance = $this->today($employee);

        if (! $attendance?->check_in_at) {
            throw ValidationException::withMessages(['attendance' => 'Please check in first.']);
        }

        if ($attendance->check_out_at) {
            throw ValidationException::withMessages(['attendance' => 'You have already checked out today.']);
        }

        $now = \App\Support\HrClock::now();
        $worked = (int) $attendance->check_in_at->diffInMinutes($now, true);

        $attendance->update([
            'check_out_at' => $now,
            'check_out_ip' => $ip,
            'worked_minutes' => $worked,
            'status' => $this->statusFor($employee->effectiveShift(), $worked),
        ]);

        return $attendance;
    }

    /**
     * HR sets one day directly. With times, the status follows the shift
     * rules unless HR picks one; `absent` clears the times.
     *
     * @param  array{date: string, check_in?: ?string, check_out?: ?string, status?: ?string, note?: ?string}  $data
     */
    public function saveByAdmin(Employee $employee, array $data, User $actor, string $source = 'admin'): Attendance
    {
        $date = Carbon::parse($data['date'])->startOfDay();

        if ($date->gt(\App\Support\HrClock::today())) {
            throw ValidationException::withMessages(['date' => 'Attendance cannot be marked for a future date.']);
        }

        $status = $data['status'] ?? null;
        $in = ! empty($data['check_in']) && $status !== 'absent' ? $date->copy()->setTimeFromTimeString($data['check_in']) : null;
        $out = ! empty($data['check_out']) && $status !== 'absent' ? $date->copy()->setTimeFromTimeString($data['check_out']) : null;

        if ($in && $out && $out->lte($in)) {
            throw ValidationException::withMessages(['check_out' => 'Check-out must be after check-in.']);
        }

        if (! $status && ! $in) {
            throw ValidationException::withMessages(['check_in' => 'Enter a check-in time or choose a status.']);
        }

        $shift = $employee->effectiveShift();
        $worked = $in && $out ? (int) $in->diffInMinutes($out, true) : 0;

        return Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date->toDateString()],
            [
                'check_in_at' => $in,
                'check_out_at' => $out,
                'worked_minutes' => $worked,
                'is_late' => $in && $shift ? $this->isLate($shift, $in) : false,
                'status' => $status ?: ($out ? $this->statusFor($shift, $worked) : 'present'),
                'note' => $data['note'] ?? null,
                'source' => $source,
                'updated_by' => $actor->id,
            ],
        );
    }

    /**
     * @param  array{date: string, check_in_time: string, check_out_time: string, reason: string}  $data
     */
    public function requestRegularization(Employee $employee, array $data): AttendanceRegularization
    {
        $date = Carbon::parse($data['date']);

        if ($date->gt(\App\Support\HrClock::today())) {
            throw ValidationException::withMessages(['date' => 'You can only correct a past or today\'s date.']);
        }

        if ($date->lt($employee->joining_date)) {
            throw ValidationException::withMessages(['date' => 'That date is before your joining date.']);
        }

        $pending = AttendanceRegularization::where('employee_id', $employee->id)
            ->whereDate('date', $date)->where('status', 'pending')->exists();

        if ($pending) {
            throw ValidationException::withMessages(['date' => 'A correction for this date is already waiting for approval.']);
        }

        return AttendanceRegularization::create([
            'employee_id' => $employee->id,
            'date' => $date->toDateString(),
            'check_in_time' => $data['check_in_time'],
            'check_out_time' => $data['check_out_time'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);
    }

    public function approveRegularization(AttendanceRegularization $request, User $actor, ?string $note = null): void
    {
        $this->assertPending($request);

        DB::transaction(function () use ($request, $actor, $note) {
            $this->saveByAdmin($request->employee, [
                'date' => $request->date->toDateString(),
                'check_in' => $request->check_in_time,
                'check_out' => $request->check_out_time,
                'note' => 'Regularized: '.str($request->reason)->limit(200),
            ], $actor, 'regularization');

            $request->update(['status' => 'approved', 'reviewed_by' => $actor->id, 'reviewed_at' => \App\Support\HrClock::now(), 'review_note' => $note]);
        });

        $this->mail->notice($request->employee->user, 'Attendance correction approved', [
            'Your attendance correction for '.$request->date->format('d M Y').' has been approved.',
        ], 'View attendance', route('employee.attendance.index'));
    }

    public function rejectRegularization(AttendanceRegularization $request, User $actor, ?string $note = null): void
    {
        $this->assertPending($request);

        $request->update(['status' => 'rejected', 'reviewed_by' => $actor->id, 'reviewed_at' => \App\Support\HrClock::now(), 'review_note' => $note]);

        $this->mail->notice($request->employee->user, 'Attendance correction rejected', array_values(array_filter([
            'Your attendance correction for '.$request->date->format('d M Y').' was not approved.',
            $note ? 'Note from HR: '.$note : null,
        ])), 'View attendance', route('employee.attendance.index'));
    }

    /**
     * Nightly clean-up for a past day: anyone who checked in but never checked
     * out is closed as a half day, so a forgotten check-out never reads as a
     * full day. They can ask for a correction.
     */
    public function closeDay(Carbon $date): int
    {
        return Attendance::whereDate('date', $date)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->where('status', 'present')
            ->update(['status' => 'half_day', 'note' => 'No check-out recorded', 'source' => 'system']);
    }

    /** @return list<string> */
    public function allowedIps(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->settings->get(self::IP_SETTING)))));
    }

    public function statusFor(?Shift $shift, int $workedMinutes): string
    {
        $full = $shift?->full_day_minutes ?? 480;
        $half = $shift?->half_day_minutes ?? 240;

        return match (true) {
            $workedMinutes >= $full => 'present',
            $workedMinutes >= $half => 'half_day',
            default => 'absent',
        };
    }

    private function isLate(Shift $shift, Carbon $checkIn): bool
    {
        $start = $checkIn->copy()->setTimeFromTimeString((string) $shift->start_time);

        return $checkIn->gt($start->addMinutes($shift->grace_minutes));
    }

    private function assertAllowedIp(string $ip): void
    {
        $allowed = $this->allowedIps();

        if ($allowed !== [] && ! in_array($ip, $allowed, true)) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance can only be marked from the office network. Your IP address: '.$ip,
            ]);
        }
    }

    private function assertPending(AttendanceRegularization $request): void
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'This request has already been reviewed.']);
        }
    }
}
