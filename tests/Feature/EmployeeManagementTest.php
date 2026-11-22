<?php

namespace Tests\Feature;

use App\Models\Hr\Attendance;
use App\Models\Hr\Employee;
use App\Models\Hr\Holiday;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Models\Hr\PayrollRun;
use App\Models\Hr\SalaryAdvance;
use App\Models\User;
use App\Notifications\EmployeeCredentialsNotification;
use App\Notifications\HrNoticeNotification;
use App\Services\Hr\PayrollService;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Employee Management (HR): creating employees with emailed credentials,
 * the employee portal, attendance, leave, payroll, and HR access control.
 */
class EmployeeManagementTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function superAdmin(): User
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('super_admin');

        return $admin;
    }

    /** @param array<string, mixed> $overrides */
    private function createEmployee(array $overrides = [], ?array $salary = null): Employee
    {
        $this->actingAs($this->superAdmin())->post('/admin/hr/employees', array_merge([
            'name' => 'Ravi Kumar',
            'email' => 'ravi'.uniqid().'@example.com',
            'employment_type' => 'full_time',
            'joining_date' => '2026-09-01',
            'salary' => $salary ?? [],
        ], $overrides))->assertSessionHasNoErrors();

        auth()->logout();

        return Employee::latest('id')->firstOrFail();
    }

    // ------------------------------------------------------------- creation

    public function test_admin_creates_an_employee_and_the_login_is_emailed(): void
    {
        Notification::fake();

        $employee = $this->createEmployee(['email' => 'ravi@example.com', 'password' => 'Secret#123']);

        $this->assertSame('GL-EMP-0001', $employee->employee_code);
        $this->assertTrue($employee->user->hasRole('employee'));
        $this->assertSame('active', $employee->user->status);

        Notification::assertSentTo($employee->user, EmployeeCredentialsNotification::class, function ($n) {
            return $n->employeeCode === 'GL-EMP-0001' && $n->password === 'Secret#123';
        });

        $second = $this->createEmployee();
        $this->assertSame('GL-EMP-0002', $second->employee_code);
    }

    public function test_a_password_is_generated_when_none_is_typed(): void
    {
        Notification::fake();

        $employee = $this->createEmployee();

        Notification::assertSentTo($employee->user, EmployeeCredentialsNotification::class, function ($n) use ($employee) {
            return strlen($n->password) === 10 && \Hash::check($n->password, $employee->user->password);
        });
    }

    public function test_the_employee_can_log_in_with_employee_id_or_email(): void
    {
        $employee = $this->createEmployee(['email' => 'asha@example.com', 'password' => 'Secret#123']);

        $this->post('/login', ['email' => strtolower($employee->employee_code), 'password' => 'Secret#123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($employee->user);

        auth()->logout();

        $this->post('/login', ['email' => 'asha@example.com', 'password' => 'Secret#123'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect(route('employee.dashboard'));
    }

    public function test_a_wrong_password_with_an_employee_id_is_rejected(): void
    {
        $employee = $this->createEmployee(['password' => 'Secret#123']);

        $this->post('/login', ['email' => $employee->employee_code, 'password' => 'wrong-pass'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_resetting_a_password_emails_the_new_one(): void
    {
        Notification::fake();
        $employee = $this->createEmployee();

        $this->actingAs($this->superAdmin())
            ->put("/admin/hr/employees/{$employee->id}/password", ['password' => 'NewPass#456'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(\Hash::check('NewPass#456', $employee->user->fresh()->password));
        Notification::assertSentTo($employee->user, EmployeeCredentialsNotification::class, fn ($n) => $n->isReset && $n->password === 'NewPass#456');
    }

    public function test_leaving_blocks_the_login(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($this->superAdmin())
            ->patch("/admin/hr/employees/{$employee->id}/status", ['employment_status' => 'resigned', 'exit_date' => '2026-11-30'])
            ->assertSessionHasNoErrors();

        $this->assertSame('blocked', $employee->user->fresh()->status);
        $this->assertSame('2026-11-30', $employee->fresh()->exit_date->toDateString());
    }

    // ------------------------------------------------------------- pages

    public function test_every_employee_portal_page_renders(): void
    {
        $employee = $this->createEmployee([], ['basic' => 20000]);

        $this->actingAs($employee->user);
        foreach (['/employee/dashboard', '/employee/profile', '/employee/attendance', '/employee/leaves', '/employee/payslips', '/employee/expenses'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/employee/team-leaves')->assertForbidden();
    }

    public function test_every_admin_hr_page_renders(): void
    {
        $employee = $this->createEmployee([], ['basic' => 20000]);
        $admin = $this->superAdmin();

        foreach (['/admin/hr', '/admin/hr/employees', '/admin/hr/employees/create', "/admin/hr/employees/{$employee->id}",
            "/admin/hr/employees/{$employee->id}/edit", '/admin/hr/organisation', '/admin/hr/shifts', '/admin/hr/holidays',
            '/admin/hr/leave-types', '/admin/hr/announcements', '/admin/hr/attendance', '/admin/hr/attendance/report',
            '/admin/hr/attendance/corrections', '/admin/hr/leaves', '/admin/hr/payroll', '/admin/hr/payroll/advances',
            '/admin/hr/assets', '/admin/hr/expenses', '/admin/hr/employees/export', '/admin/hr/attendance/report/export'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    // ------------------------------------------------------------- access

    public function test_hr_screens_follow_the_permission_matrix(): void
    {
        $this->actingAs($this->makeSubAdmin())->get('/admin/hr/employees')->assertForbidden();
        $this->actingAs($this->makeSubAdmin())->get('/admin/hr')->assertForbidden();

        $leavesOnly = $this->makeSubAdmin(['leaves']);
        $this->actingAs($leavesOnly)->get('/admin/hr/leaves')->assertOk();
        $this->actingAs($leavesOnly)->get('/admin/hr/employees')->assertForbidden();
        $this->actingAs($leavesOnly)->get('/admin/hr/payroll')->assertForbidden();

        $employee = $this->createEmployee();
        $this->actingAs($employee->user)->get('/admin/hr/employees')->assertForbidden();
    }

    public function test_an_employee_cannot_open_someone_elses_payslip_or_documents(): void
    {
        $a = $this->createEmployee([], ['basic' => 20000]);
        $b = $this->createEmployee();

        $this->actingAs($b->user)->get(route('hr.files.photo', $a))->assertForbidden();
    }

    // ------------------------------------------------------------- attendance

    public function test_check_in_after_grace_is_late_and_check_out_sets_the_day_status(): void
    {
        $employee = $this->createEmployee();

        // Default shift 10:00, grace 15 min. 10:20 IST = 04:50 UTC.
        Carbon::setTestNow(Carbon::parse('2026-11-18 04:50:00', 'UTC'));
        $this->actingAs($employee->user)->post('/employee/attendance/check-in')->assertSessionHasNoErrors();

        $record = Attendance::firstOrFail();
        $this->assertSame('2026-11-18', $record->date->toDateString());
        $this->assertSame('10:20', $record->check_in_at->format('H:i'));
        $this->assertTrue($record->is_late);

        // 15:30 IST: 5h10m worked -> half day (full day is 8h).
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'UTC'));
        $this->post('/employee/attendance/check-out')->assertSessionHasNoErrors();
        $this->assertSame('half_day', $record->fresh()->status);
        $this->assertSame(310, $record->fresh()->worked_minutes);

        $this->post('/employee/attendance/check-out')->assertSessionHasErrors('attendance');
    }

    public function test_check_in_is_limited_to_office_ips_when_configured(): void
    {
        $employee = $this->createEmployee();
        app(\App\Services\SettingsService::class)->set('hr_attendance_ips', '10.0.0.5');

        $this->actingAs($employee->user)->post('/employee/attendance/check-in')->assertSessionHasErrors('attendance');
        $this->assertSame(0, Attendance::count());
    }

    public function test_an_approved_correction_writes_the_attendance(): void
    {
        $employee = $this->createEmployee();
        Carbon::setTestNow(Carbon::parse('2026-11-18 06:00:00', 'UTC'));

        $this->actingAs($employee->user)->post('/employee/attendance/correction', [
            'date' => '2026-11-17', 'check_in_time' => '10:00', 'check_out_time' => '18:30', 'reason' => 'Forgot to check in',
        ])->assertSessionHasNoErrors();

        $request = \App\Models\Hr\AttendanceRegularization::firstOrFail();
        $this->actingAs($this->superAdmin())->patch("/admin/hr/attendance/corrections/{$request->id}/approve")->assertSessionHasNoErrors();

        $record = Attendance::where('employee_id', $employee->id)->firstOrFail();
        $this->assertSame('present', $record->status);
        $this->assertSame(510, $record->worked_minutes);
    }

    // ------------------------------------------------------------- leave

    public function test_leave_counts_working_days_and_approval_uses_the_balance(): void
    {
        Notification::fake();
        Carbon::setTestNow(Carbon::parse('2026-11-16 06:00:00', 'UTC'));

        $manager = $this->createEmployee(['name' => 'Manager']);
        $employee = $this->createEmployee(['reporting_manager_id' => $manager->id]);
        $cl = LeaveType::where('code', 'CL')->firstOrFail();
        Holiday::create(['date' => '2026-11-21', 'name' => 'Test Holiday']);

        // Fri 20 → Mon 23: Sat is a holiday, Sun a weekly off → 2 days.
        $this->actingAs($employee->user)->post('/employee/leaves', [
            'leave_type_id' => $cl->id, 'from_date' => '2026-11-20', 'to_date' => '2026-11-23', 'reason' => 'Family function',
        ])->assertSessionHasNoErrors();

        $leave = LeaveRequest::firstOrFail();
        $this->assertSame(2.0, $leave->days);
        Notification::assertSentTo($manager->user, HrNoticeNotification::class);

        // Someone who is not the manager cannot approve it.
        $other = $this->createEmployee();
        $this->actingAs($other->user)->patch("/employee/team-leaves/{$leave->id}/approve")->assertForbidden();

        $this->actingAs($manager->user)->get('/employee/team-leaves')->assertOk()->assertSee('Family function');
        $this->actingAs($manager->user)->patch("/employee/team-leaves/{$leave->id}/approve")->assertSessionHasNoErrors();

        $this->assertSame('approved', $leave->fresh()->status);
        $this->assertSame(2.0, LeaveBalance::where(['employee_id' => $employee->id, 'leave_type_id' => $cl->id])->value('used'));
        Notification::assertSentTo($employee->user, HrNoticeNotification::class, fn ($n) => $n->subject === 'Leave approved');

        // Approved leave that has not started can be cancelled; the days return.
        $this->actingAs($employee->user)->patch("/employee/leaves/{$leave->id}/cancel")->assertSessionHasNoErrors();
        $this->assertSame(0.0, LeaveBalance::where(['employee_id' => $employee->id, 'leave_type_id' => $cl->id])->value('used'));
    }

    public function test_leave_beyond_the_balance_is_refused(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 06:00:00', 'UTC'));
        $employee = $this->createEmployee();
        $sl = LeaveType::where('code', 'SL')->firstOrFail(); // 6 days a year

        $this->actingAs($employee->user)->post('/employee/leaves', [
            'leave_type_id' => $sl->id, 'from_date' => '2026-12-01', 'to_date' => '2026-12-10', 'reason' => 'Long illness',
        ])->assertSessionHasErrors('leave_type_id');

        $this->assertSame(0, LeaveRequest::count());
    }

    // ------------------------------------------------------------- payroll

    public function test_payroll_applies_loss_of_pay_pf_and_advance_recovery(): void
    {
        Notification::fake();
        $employee = $this->createEmployee([], [
            'basic' => 20000, 'hra' => 5000, 'pf_enabled' => 1, 'professional_tax' => 200,
        ]);

        // October 2026: present every working day except Mon 5 and Tue 6.
        foreach (CarbonPeriod::create('2026-10-01', '2026-10-31') as $day) {
            if ($day->isSunday() || in_array($day->day, [5, 6], true)) {
                continue;
            }
            Attendance::create(['employee_id' => $employee->id, 'date' => $day->toDateString(), 'status' => 'present', 'worked_minutes' => 540]);
        }

        SalaryAdvance::create(['employee_id' => $employee->id, 'amount' => 5000, 'monthly_deduction' => 1000,
            'balance' => 5000, 'issued_on' => '2026-09-15', 'status' => 'active']);

        Carbon::setTestNow(Carbon::parse('2026-11-22 06:00:00', 'UTC'));
        $admin = $this->superAdmin();

        // The current month cannot be run yet.
        $this->actingAs($admin)->post('/admin/hr/payroll', ['month' => '2026-11'])->assertSessionHasErrors('month');

        $this->actingAs($admin)->post('/admin/hr/payroll', ['month' => '2026-10'])->assertSessionHasNoErrors();
        $run = PayrollRun::where('month', '2026-10')->firstOrFail();
        $slip = $run->payslips()->firstOrFail();

        $this->assertSame(29.0, $slip->paid_days);
        $this->assertSame(2.0, $slip->lop_days);
        $this->assertEqualsWithDelta(1612.90, $slip->lop_deduction, 0.01);
        $this->assertEqualsWithDelta(1800.0, $slip->pf, 0.01);        // 12% of 15,000 cap
        $this->assertEqualsWithDelta(1000.0, $slip->advance_deduction, 0.01);
        $this->assertEqualsWithDelta(25000 - 1612.90 - 1800 - 200 - 1000, $slip->net_pay, 0.01);

        // Draft payslips are not visible to the employee yet.
        $this->actingAs($employee->user)->get("/employee/payslips/{$slip->id}")->assertNotFound();

        // HR adds a bonus, then finalizes.
        $this->actingAs($admin)->patch("/admin/hr/payroll/payslips/{$slip->id}", ['bonus' => 500])->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(25000 - 1612.90 - 1800 - 200 - 1000 + 500, $slip->fresh()->net_pay, 0.01);

        $this->actingAs($admin)->post("/admin/hr/payroll/{$run->id}/finalize")->assertSessionHasNoErrors();

        $this->assertSame('finalized', $run->fresh()->status);
        $this->assertEqualsWithDelta(4000, SalaryAdvance::firstOrFail()->balance, 0.01);
        Notification::assertSentTo($employee->user, HrNoticeNotification::class, fn ($n) => str_contains($n->subject, 'payslip'));

        $this->actingAs($employee->user)->get("/employee/payslips/{$slip->id}")->assertOk()->assertSee('October 2026');

        // A finalized payroll is locked.
        $this->actingAs($admin)->post('/admin/hr/payroll', ['month' => '2026-10'])->assertSessionHasErrors('month');
    }

    public function test_days_before_joining_are_unpaid(): void
    {
        $employee = $this->createEmployee(['joining_date' => '2026-10-16'], ['basic' => 31000]);

        foreach (CarbonPeriod::create('2026-10-16', '2026-10-31') as $day) {
            if (! $day->isSunday()) {
                Attendance::create(['employee_id' => $employee->id, 'date' => $day->toDateString(), 'status' => 'present']);
            }
        }

        Carbon::setTestNow(Carbon::parse('2026-11-22 06:00:00', 'UTC'));
        $run = app(PayrollService::class)->generate('2026-10', $this->superAdmin());

        // 16 days paid of 31 → 31000 × 16/31 = 16000.
        $this->assertEqualsWithDelta(16000, $run->payslips()->firstOrFail()->net_pay, 0.01);
    }

    // ------------------------------------------------------------- assets & expenses

    public function test_assets_are_assigned_and_returned_with_history(): void
    {
        Notification::fake();
        $employee = $this->createEmployee();
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post('/admin/hr/assets', ['name' => 'Dell Laptop', 'asset_tag' => 'GL-LAP-001', 'category' => 'Laptop'])
            ->assertSessionHasNoErrors();
        $asset = \App\Models\Hr\Asset::firstOrFail();

        $this->actingAs($admin)->post("/admin/hr/assets/{$asset->id}/assign", ['employee_id' => $employee->id, 'assigned_on' => '2026-11-01'])
            ->assertSessionHasNoErrors();
        $this->assertSame('assigned', $asset->fresh()->status);
        $this->actingAs($employee->user)->get('/employee/profile')->assertSee('GL-LAP-001');

        $this->actingAs($admin)->post("/admin/hr/assets/{$asset->id}/return", ['returned_on' => '2026-11-10', 'new_status' => 'available'])
            ->assertSessionHasNoErrors();
        $this->assertSame('available', $asset->fresh()->status);
        $this->assertNotNull($asset->assignments()->first()->returned_on);
    }

    public function test_expense_claim_flow(): void
    {
        Notification::fake();
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)->post('/employee/expenses', [
            'title' => 'Client visit taxi', 'category' => 'Travel', 'amount' => 450, 'expense_date' => '2026-11-01',
        ])->assertSessionHasNoErrors();

        $claim = \App\Models\Hr\ExpenseClaim::firstOrFail();
        $admin = $this->superAdmin();

        $this->actingAs($admin)->patch("/admin/hr/expenses/{$claim->id}/paid")->assertSessionHasErrors('status');
        $this->actingAs($admin)->patch("/admin/hr/expenses/{$claim->id}/approve")->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch("/admin/hr/expenses/{$claim->id}/paid")->assertSessionHasNoErrors();

        $this->assertSame('paid', $claim->fresh()->status);
        Notification::assertSentTo($employee->user, HrNoticeNotification::class, fn ($n) => $n->subject === 'Expense reimbursed');
    }
}
