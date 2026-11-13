<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AccountCreatedNotification;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
use App\Services\CommissionPartnerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Forgot password (emailed link), change-your-own-password for every role, and
 * the account emails that go with them.
 */
class PasswordManagementTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Notification::fake();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole($role);

        return $user;
    }

    private function fakeSession(User $user, string $id): void
    {
        DB::table('sessions')->insert([
            'id' => $id, 'user_id' => $user->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'payload' => '', 'last_activity' => time(),
        ]);
    }

    /** @return array<string, array{string}> */
    public static function roles(): array
    {
        return collect(['super_admin', 'admin', 'sub_admin', 'branch_manager', 'commission_partner', 'vip_member', 'customer'])
            ->mapWithKeys(fn ($r) => [$r => [$r]])->all();
    }

    // --- Forgot password -------------------------------------------------

    public function test_forgot_password_emails_the_branded_reset_link(): void
    {
        $user = $this->userWithRole('branch_manager');

        $this->get('/forgot-password')->assertOk();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_the_reset_email_renders_with_the_link(): void
    {
        $user = $this->userWithRole('vip_member');
        $mail = (new ResetPasswordNotification('tok123'))->toMail($user);

        $html = (string) view($mail->view, $mail->viewData);
        $this->assertStringContainsString('tok123', $html);
        $this->assertStringContainsString('Reset password', $html);
    }

    public function test_resetting_with_the_token_changes_password_drops_sessions_and_notifies(): void
    {
        $user = $this->userWithRole('commission_partner');
        $this->fakeSession($user, 'old-session');
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'brand-new-pass-1',
            'password_confirmation' => 'brand-new-pass-1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('brand-new-pass-1', $user->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'old-session']);
        Notification::assertSentTo($user, PasswordChangedNotification::class);
    }

    // --- Change own password --------------------------------------------

    #[DataProvider('roles')]
    public function test_every_role_can_open_change_password(string $role): void
    {
        $this->actingAs($this->userWithRole($role))
            ->get(route('account.password.edit'))
            ->assertOk()
            ->assertSee('name="current_password"', false)
            ->assertSee(route('account.password.update'), false);
    }

    public function test_guests_cannot_open_change_password(): void
    {
        $this->get(route('account.password.edit'))->assertRedirect(route('login'));
    }

    #[DataProvider('roles')]
    public function test_every_role_can_change_their_password(string $role): void
    {
        $user = $this->userWithRole($role);
        $this->fakeSession($user, 'other-device');

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'password',
                'password' => 'new-secret-99',
                'password_confirmation' => 'new-secret-99',
            ])
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-secret-99', $user->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'other-device']);
        Notification::assertSentTo($user, PasswordChangedNotification::class, fn ($n) => $n->changedBy === null);
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = $this->userWithRole('vip_member');

        $this->actingAs($user)
            ->from(route('account.password.edit'))
            ->put(route('account.password.update'), [
                'current_password' => 'not-it',
                'password' => 'new-secret-99',
                'password_confirmation' => 'new-secret-99',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        Notification::assertNothingSent();
    }

    public function test_mismatched_confirmation_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->put(route('account.password.update'), [
                'current_password' => 'password',
                'password' => 'new-secret-99',
                'password_confirmation' => 'something-else',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_fortify_user_password_route_is_disabled(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->put('/user/password', [])
            ->assertNotFound();
    }

    // --- Emails from elsewhere ------------------------------------------

    public function test_branch_manager_resetting_a_partner_password_notifies_the_partner(): void
    {
        $partner = $this->userWithRole('commission_partner');

        app(CommissionPartnerService::class)->setPassword($partner, 'reset-by-bm-1');

        Notification::assertSentTo($partner, PasswordChangedNotification::class,
            fn ($n) => $n->changedBy === 'your Branch Manager');
    }

    public function test_creating_a_partner_sends_a_welcome_email(): void
    {
        $bm = $this->makeBranchManager(30);

        $partner = app(CommissionPartnerService::class)->createPartner([
            'name' => 'New Partner', 'email' => 'np@example.com', 'password' => 'secret-123',
        ], 10, $bm);

        Notification::assertSentTo($partner, AccountCreatedNotification::class,
            fn ($n) => $n->createdBy === $bm->name);
    }

    public function test_account_emails_render(): void
    {
        $user = $this->userWithRole('vip_member');

        foreach ([new AccountCreatedNotification('Someone'), new PasswordChangedNotification('an administrator')] as $n) {
            $mail = $n->toMail($user);
            $html = (string) view($mail->view, $mail->viewData);
            $this->assertStringContainsString($user->name, $html);
            $this->assertStringNotContainsString('secret', $html);
        }
    }
}
