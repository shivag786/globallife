<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * VIP members withdrawing their wallet: the ₹500 floor, the 24-hour cooldown,
 * and the admin's manual "mark as paid" with UTR / screenshot proof.
 *
 * No payment gateway is involved — the admin transfers by hand and records
 * evidence, which is what the member is shown.
 */
class VipWithdrawalTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * A VIP member whose wallet holds the given balance.
     *
     * @return array{0: User, 1: Wallet}
     */
    private function memberWithBalance(float $balance): array
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        $wallet = Wallet::create(['user_id' => $member->id, 'balance' => $balance]);

        return [$member, $wallet];
    }

    private function superAdmin(): User
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('super_admin');

        return $admin;
    }

    public function test_a_member_can_request_a_withdrawal_and_the_wallet_is_not_debited_yet(): void
    {
        [$member, $wallet] = $this->memberWithBalance(2500);

        $this->actingAs($member)->post('/wallet/withdrawals', ['amount' => 1500])
            ->assertRedirect()
            ->assertSessionHas('status');

        $request = WithdrawalRequest::where('user_id', $member->id)->sole();
        $this->assertSame('pending', $request->status);
        $this->assertEqualsWithDelta(1500, (float) $request->amount, 0.01);
        $this->assertEqualsWithDelta(2500, (float) $request->wallet_balance_at_request, 0.01);

        // Money only moves when the admin actually pays it.
        $this->assertEqualsWithDelta(2500, (float) $wallet->refresh()->balance, 0.01);
    }

    public function test_below_the_five_hundred_rupee_minimum_is_refused(): void
    {
        $this->assertSame(500, WithdrawalService::MINIMUM_AMOUNT);

        // Enough balance, but asking for too little.
        [$rich] = $this->memberWithBalance(2000);
        $this->actingAs($rich)->post('/wallet/withdrawals', ['amount' => 499])
            ->assertSessionHasErrors('amount');
        $this->assertSame(0, WithdrawalRequest::count());

        // Not enough balance to reach the floor at all.
        [$poor] = $this->memberWithBalance(480);
        $this->actingAs($poor)->post('/wallet/withdrawals', ['amount' => 480])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame(0, WithdrawalRequest::count());

        $this->actingAs($poor)->get('/wallet')
            ->assertOk()
            ->assertSee('You need at least ₹500.00 to request a withdrawal.', false);
    }

    public function test_more_than_the_wallet_holds_is_refused(): void
    {
        [$member] = $this->memberWithBalance(900);

        $this->actingAs($member)->post('/wallet/withdrawals', ['amount' => 1200])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, WithdrawalRequest::count());
    }

    public function test_a_second_request_within_24_hours_is_refused_with_the_try_later_message(): void
    {
        [$member] = $this->memberWithBalance(5000);

        $this->actingAs($member)->post('/wallet/withdrawals', ['amount' => 600])
            ->assertSessionHas('status');

        // The button stays enabled, so pressing it again must explain itself.
        $this->actingAs($member)->post('/wallet/withdrawals', ['amount' => 600])
            ->assertRedirect()
            ->assertSessionHas('error', 'Withdrawal request is already available, kindly try after 24 hours.');

        $this->assertSame(1, WithdrawalRequest::count());

        // The form is still rendered, not hidden or disabled.
        $this->actingAs($member)->get('/wallet')
            ->assertOk()
            ->assertSee('Request Withdrawal', false);
    }

    public function test_the_cooldown_message_wins_over_an_invalid_amount(): void
    {
        [$member] = $this->memberWithBalance(5000);
        $this->actingAs($member)->post('/wallet/withdrawals', ['amount' => 600]);

        // Even with a nonsense amount, the reason reported is the open request.
        $this->actingAs($member)->post('/wallet/withdrawals', ['amount' => 1])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('error', 'Withdrawal request is already available, kindly try after 24 hours.');
    }

    public function test_a_new_request_is_allowed_once_24_hours_have_passed_and_the_last_was_paid(): void
    {
        [$member, $wallet] = $this->memberWithBalance(5000);
        $admin = $this->superAdmin();

        $first = app(WithdrawalService::class)->request($member, 600);
        app(WithdrawalService::class)->markPaid($first, $admin, '4312987');

        // Age the request past the cooldown.
        $first->forceFill(['created_at' => now()->subHours(25)])->save();

        $this->actingAs($member)->post('/wallet/withdrawals', ['amount' => 700])
            ->assertSessionHas('status');

        $this->assertSame(2, WithdrawalRequest::where('user_id', $member->id)->count());
        $this->assertEqualsWithDelta(4400, (float) $wallet->refresh()->balance, 0.01);
    }

    public function test_a_pending_request_still_blocks_after_the_cooldown(): void
    {
        [$member] = $this->memberWithBalance(5000);

        $request = app(WithdrawalService::class)->request($member, 600);
        $request->forceFill(['created_at' => now()->subHours(30)])->save();

        $this->actingAs($member)->post('/wallet/withdrawals', ['amount' => 600])
            ->assertSessionHas('error');

        $this->assertSame(1, WithdrawalRequest::count());
    }

    public function test_only_vip_members_may_request_partners_are_settled_monthly_instead(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        Wallet::create(['user_id' => $partner->id, 'balance' => 9000]);

        $this->actingAs($partner)->post('/wallet/withdrawals', ['amount' => 1000])->assertForbidden();

        $this->assertSame(0, WithdrawalRequest::count());

        // Their wallet page offers no withdrawal form at all.
        $this->actingAs($partner)->get('/wallet')->assertOk()->assertDontSee('Request Withdrawal', false);
    }

    public function test_marking_paid_needs_a_utr_or_a_screenshot(): void
    {
        [$member, $wallet] = $this->memberWithBalance(2000);
        $request = app(WithdrawalService::class)->request($member, 800);
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->patch("/admin/withdrawals/{$request->id}/mark-paid", [])
            ->assertSessionHasErrors(['utr_number', 'payment_screenshot']);

        $this->assertSame('pending', $request->refresh()->status);
        $this->assertEqualsWithDelta(2000, (float) $wallet->refresh()->balance, 0.01);
    }

    public function test_marking_paid_with_a_utr_debits_the_wallet_and_shows_the_member_the_proof(): void
    {
        [$member, $wallet] = $this->memberWithBalance(2000);
        $request = app(WithdrawalService::class)->request($member, 800);
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->patch("/admin/withdrawals/{$request->id}/mark-paid", [
                'utr_number' => '431298765432',
                'admin_note' => 'Sent via IMPS',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $request->refresh();
        $this->assertSame('paid', $request->status);
        $this->assertSame('431298765432', $request->utr_number);
        $this->assertSame($admin->id, $request->paid_by);
        $this->assertNotNull($request->paid_at);

        // 2000 − 800
        $this->assertEqualsWithDelta(1200, (float) $wallet->refresh()->balance, 0.01);

        // The member now gets View Detail and the UTR inside it.
        $this->actingAs($member)->get('/wallet')
            ->assertOk()
            ->assertSee('View Detail', false)
            ->assertSee('431298765432', false)
            ->assertSee('Sent via IMPS', false);
    }

    public function test_marking_paid_with_a_screenshot_stores_it_and_the_member_can_see_it(): void
    {
        Storage::fake('public');

        [$member] = $this->memberWithBalance(3000);
        $request = app(WithdrawalService::class)->request($member, 1000);
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->patch("/admin/withdrawals/{$request->id}/mark-paid", [
                'payment_screenshot' => UploadedFile::fake()->image('utr.jpg', 600, 400),
            ])
            ->assertSessionHasNoErrors();

        $request->refresh();
        $this->assertNotNull($request->payment_screenshot_path);
        Storage::disk('public')->assertExists($request->payment_screenshot_path);

        $this->actingAs($member)->get('/wallet')
            ->assertOk()
            ->assertSee($request->payment_screenshot_path, false);
    }

    public function test_view_detail_stays_hidden_while_the_request_is_pending(): void
    {
        [$member] = $this->memberWithBalance(2000);
        app(WithdrawalService::class)->request($member, 800);

        $this->actingAs($member)->get('/wallet')
            ->assertOk()
            ->assertSee('Pending', false)
            ->assertSee('Awaiting payment from the admin', false)
            ->assertDontSee('View Detail', false);
    }

    public function test_a_request_cannot_be_paid_twice(): void
    {
        [$member, $wallet] = $this->memberWithBalance(2000);
        $request = app(WithdrawalService::class)->request($member, 800);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->patch("/admin/withdrawals/{$request->id}/mark-paid", ['utr_number' => 'A1']);
        $this->actingAs($admin)->patch("/admin/withdrawals/{$request->id}/mark-paid", ['utr_number' => 'B2'])
            ->assertSessionHas('error');

        // Debited once only.
        $this->assertEqualsWithDelta(1200, (float) $wallet->refresh()->balance, 0.01);
        $this->assertSame('A1', $request->refresh()->utr_number);
    }

    public function test_the_admin_table_lists_who_asked_and_how_much(): void
    {
        [$member] = $this->memberWithBalance(2000);
        $member->update(['name' => 'Asha Vip']);
        app(WithdrawalService::class)->request($member, 750);

        $this->actingAs($this->superAdmin())->get('/admin/withdrawals')
            ->assertOk()
            ->assertSee('Asha Vip', false)
            ->assertSee($member->email, false)
            ->assertSee('750.00', false)
            ->assertSee('Mark as Paid', false);
    }

    public function test_the_admin_dashboard_box_counts_pending_requests_and_links_to_the_queue(): void
    {
        [$memberA] = $this->memberWithBalance(2000);
        [$memberB] = $this->memberWithBalance(3000);
        app(WithdrawalService::class)->request($memberA, 600);
        app(WithdrawalService::class)->request($memberB, 900);

        $this->actingAs($this->superAdmin())->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Withdrawal Requests from VIP Members', false)
            ->assertSee(route('admin.withdrawals.index', ['status' => 'pending']), false)
            ->assertSee('1,500.00 awaiting payment', false);
    }

    public function test_a_vip_member_cannot_reach_the_admin_queue(): void
    {
        [$member] = $this->memberWithBalance(2000);
        $request = app(WithdrawalService::class)->request($member, 800);

        $this->actingAs($member)->get('/admin/withdrawals')->assertForbidden();
        $this->actingAs($member)
            ->patch("/admin/withdrawals/{$request->id}/mark-paid", ['utr_number' => 'X'])
            ->assertForbidden();

        $this->assertSame('pending', $request->refresh()->status);
    }
}
