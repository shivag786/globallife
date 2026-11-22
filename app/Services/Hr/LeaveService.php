<?php

namespace App\Services\Hr;

use App\Models\Hr\Employee;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Leave: balances, applying, approval by HR or the reporting manager, and
 * cancelling.
 *
 * Days are counted on working days only (weekly offs and holidays inside the
 * range are free). A balance row per employee/type/year is created on first
 * use with the type's yearly quota, plus whatever carries forward from the
 * previous year when the type allows it. `used` moves only on approval and
 * on cancelling an approved leave.
 */
class LeaveService
{
    public function __construct(
        private readonly WorkCalendarService $calendar,
        private readonly HrMailService $mail,
    ) {}

    public function countDays(Employee $employee, Carbon $from, Carbon $to, bool $halfDay = false): float
    {
        $days = 0;

        foreach (CarbonPeriod::create($from, $to) as $date) {
            if ($this->calendar->isWorkingDay($employee, $date)) {
                $days++;
            }
        }

        return $halfDay ? min($days, 1) * 0.5 : (float) $days;
    }

    public function balance(Employee $employee, LeaveType $type, int $year): LeaveBalance
    {
        $existing = LeaveBalance::where(['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => $year])->first();

        if ($existing) {
            return $existing;
        }

        $carried = 0.0;
        if ($type->carry_forward) {
            $previous = LeaveBalance::where(['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => $year - 1])->first();
            $carried = $previous ? min($previous->remaining(), $type->max_carry_forward) : 0.0;
        }

        return LeaveBalance::firstOrCreate(
            ['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => $year],
            ['allotted' => $type->days_per_year, 'carried_forward' => $carried, 'used' => 0],
        );
    }

    /**
     * Balance of every active quota-based type, with days already waiting
     * for approval.
     *
     * @return Collection<int, array{type: LeaveType, balance: LeaveBalance, pending: float, available: float}>
     */
    public function balances(Employee $employee, ?int $year = null): Collection
    {
        $year ??= (int) \App\Support\HrClock::now()->year;

        return LeaveType::where('is_active', true)->orderBy('name')->get()
            ->filter(fn (LeaveType $type) => $type->hasQuota())
            ->map(function (LeaveType $type) use ($employee, $year) {
                $balance = $this->balance($employee, $type, $year);
                $pending = $this->pendingDays($employee, $type, $year);

                return [
                    'type' => $type,
                    'balance' => $balance,
                    'pending' => $pending,
                    'available' => max(0, $balance->remaining() - $pending),
                ];
            })
            ->values();
    }

    /**
     * @param  array{leave_type_id: int, from_date: string, to_date?: ?string, is_half_day?: bool, reason: string}  $data
     */
    public function apply(Employee $employee, array $data): LeaveRequest
    {
        $type = LeaveType::where('is_active', true)->findOrFail($data['leave_type_id']);
        $halfDay = (bool) ($data['is_half_day'] ?? false);
        $from = Carbon::parse($data['from_date'])->startOfDay();
        $to = $halfDay || empty($data['to_date']) ? $from->copy() : Carbon::parse($data['to_date'])->startOfDay();

        if ($to->lt($from)) {
            throw ValidationException::withMessages(['to_date' => 'The end date must be on or after the start date.']);
        }

        if ($from->year !== $to->year) {
            throw ValidationException::withMessages(['to_date' => 'Please apply separately for each calendar year.']);
        }

        if ($from->lt($employee->joining_date)) {
            throw ValidationException::withMessages(['from_date' => 'That date is before your joining date.']);
        }

        $days = $this->countDays($employee, $from, $to, $halfDay);

        if ($days <= 0) {
            throw ValidationException::withMessages(['from_date' => 'The selected dates are all holidays or weekly offs.']);
        }

        $overlap = $employee->leaveRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->where('from_date', '<=', $to->toDateString())
            ->where('to_date', '>=', $from->toDateString())
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['from_date' => 'You already have a leave request covering these dates.']);
        }

        $this->assertAvailable($employee, $type, $from->year, $days);

        $request = $employee->leaveRequests()->create([
            'leave_type_id' => $type->id,
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'is_half_day' => $halfDay,
            'days' => $days,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        $manager = $employee->reportingManager?->user;
        if ($manager) {
            $this->mail->notice($manager, 'Leave request from '.$employee->name, [
                "{$employee->name} ({$employee->employee_code}) has applied for {$type->name}: ".$request->periodLabel()." - {$days} day(s).",
                'Reason: '.$data['reason'],
            ], 'Review request', route('employee.team-leaves.index'));
        }

        return $request;
    }

    public function approve(LeaveRequest $request, User $actor, ?string $note = null): void
    {
        DB::transaction(function () use ($request, $actor, $note) {
            $request = LeaveRequest::lockForUpdate()->findOrFail($request->id);
            $this->assertPending($request);

            $type = $request->leaveType;
            if ($type->hasQuota()) {
                $balance = $this->balance($request->employee, $type, $request->from_date->year);

                if ($balance->remaining() < $request->days) {
                    throw ValidationException::withMessages([
                        'status' => "Not enough {$type->name} balance: {$balance->remaining()} day(s) left, {$request->days} requested.",
                    ]);
                }

                $balance->increment('used', $request->days);
            }

            $request->update(['status' => 'approved', 'reviewed_by' => $actor->id, 'reviewed_at' => \App\Support\HrClock::now(), 'review_note' => $note]);
        });

        $request->refresh();
        $this->mail->notice($request->employee->user, 'Leave approved', array_values(array_filter([
            "Your {$request->leaveType->name} for {$request->periodLabel()} ({$request->days} day(s)) has been approved.",
            $note ? 'Note: '.$note : null,
        ])), 'View my leaves', route('employee.leaves.index'));
    }

    public function reject(LeaveRequest $request, User $actor, ?string $note = null): void
    {
        $this->assertPending($request);

        $request->update(['status' => 'rejected', 'reviewed_by' => $actor->id, 'reviewed_at' => \App\Support\HrClock::now(), 'review_note' => $note]);

        $this->mail->notice($request->employee->user, 'Leave request rejected', array_values(array_filter([
            "Your {$request->leaveType->name} request for {$request->periodLabel()} was not approved.",
            $note ? 'Reason: '.$note : null,
        ])), 'View my leaves', route('employee.leaves.index'));
    }

    /**
     * The employee withdraws a request: any pending one, or an approved one
     * that has not started yet (its days go back to the balance).
     */
    public function cancel(LeaveRequest $request): void
    {
        DB::transaction(function () use ($request) {
            if ($request->status === 'approved') {
                if (! $request->from_date->isAfter(\App\Support\HrClock::today())) {
                    throw ValidationException::withMessages(['status' => 'A leave that has already started cannot be cancelled. Please contact HR.']);
                }

                if ($request->leaveType->hasQuota()) {
                    $balance = $this->balance($request->employee, $request->leaveType, $request->from_date->year);
                    $balance->update(['used' => max(0, $balance->used - $request->days)]);
                }
            } elseif ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'This request can no longer be cancelled.']);
            }

            $request->update(['status' => 'cancelled']);
        });
    }

    /**
     * HR with `leaves.approve`, or the employee's own reporting manager.
     */
    public function canReview(User $user, LeaveRequest $request): bool
    {
        if ($user->can('leaves.approve')) {
            return true;
        }

        $reviewer = $user->employee;

        return $reviewer !== null
            && $request->employee->reporting_manager_id === $reviewer->id
            && $request->employee_id !== $reviewer->id;
    }

    private function pendingDays(Employee $employee, LeaveType $type, int $year): float
    {
        return (float) $employee->leaveRequests()
            ->where('leave_type_id', $type->id)
            ->where('status', 'pending')
            ->whereYear('from_date', $year)
            ->sum('days');
    }

    private function assertAvailable(Employee $employee, LeaveType $type, int $year, float $days): void
    {
        if (! $type->hasQuota()) {
            return;
        }

        $available = $this->balance($employee, $type, $year)->remaining() - $this->pendingDays($employee, $type, $year);

        if ($available < $days) {
            throw ValidationException::withMessages([
                'leave_type_id' => "Not enough {$type->name} balance. Available: ".max(0, $available)." day(s), requested: {$days}.",
            ]);
        }
    }

    private function assertPending(LeaveRequest $request): void
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'This request has already been reviewed.']);
        }
    }
}
