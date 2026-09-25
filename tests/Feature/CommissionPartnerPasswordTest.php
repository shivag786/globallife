<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * A Branch Manager setting one of their Commission Partners' passwords, from the
 * partner edit screen and nowhere else.
 *
 * Like the admin-side Branch Manager reset, this cuts off the old credentials:
 * stored sessions are dropped and the remember-me token rotated.
 */
class CommissionPartnerPasswordTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_the_change_password_form_is_on_the_partner_edit_screen(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);

        $response = $this->actingAs($branch)
            ->get("/branch/commission-partners/{$partner->id}/edit");

        $response->assertOk();
        $response->assertSee('Change Password', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
        $response->assertSee(route('branch.commission-partners.password.update', $partner), false);
    }

    public function test_the_create_screen_and_the_list_offer_no_password_control(): void
    {
        $branch = $this->makeBranchManager(30);
        $this->makeCommissionPartner($branch, 25);

        $this->actingAs($branch)->get('/branch/commission-partners/create')
            ->assertOk()
            ->assertDontSee('Change Password', false)
            ->assertDontSee('password_confirmation', false);

        $this->actingAs($branch)->get('/branch/commission-partners')
            ->assertOk()
            ->assertDontSee('Change Password', false)
            ->assertDontSee('/password', false);
    }

    public function test_a_branch_manager_can_set_their_partners_password(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        $originalHash = $partner->password;

        $this->actingAs($branch)
            ->put("/branch/commission-partners/{$partner->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertRedirect(route('branch.commission-partners.edit', $partner))
            ->assertSessionHas('status');

        $partner->refresh();

        $this->assertTrue(Hash::check('a-brand-new-password', $partner->password));
        $this->assertNotSame($originalHash, $partner->password);
    }

    public function test_the_reset_signs_the_partner_out_everywhere(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        $other = $this->makeCommissionPartner($branch, 25);

        $partner->forceFill(['remember_token' => 'old-remember-token'])->save();

        foreach ([$partner->id, $partner->id, $other->id] as $i => $userId) {
            DB::table('sessions')->insert([
                'id' => 'cp-session-'.$i,
                'user_id' => $userId,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'x',
                'last_activity' => time(),
            ]);
        }

        $this->actingAs($branch)
            ->put("/branch/commission-partners/{$partner->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ]);

        $this->assertSame(0, DB::table('sessions')->where('user_id', $partner->id)->count());
        $this->assertSame(1, DB::table('sessions')->where('user_id', $other->id)->count());
        $this->assertNotSame('old-remember-token', $partner->refresh()->remember_token);
    }

    public function test_the_two_passwords_have_to_match(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        $originalHash = $partner->password;

        $this->actingAs($branch)
            ->put("/branch/commission-partners/{$partner->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-different-password',
            ])
            ->assertSessionHasErrors('password');

        $this->assertSame($originalHash, $partner->refresh()->password);
    }

    public function test_a_short_password_is_refused(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        $originalHash = $partner->password;

        $this->actingAs($branch)
            ->put("/branch/commission-partners/{$partner->id}/password", [
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');

        $this->assertSame($originalHash, $partner->refresh()->password);
    }

    public function test_a_branch_manager_cannot_reset_someone_elses_partner(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $outsider = $this->makeBranchManager(30);
        $originalHash = $partner->password;

        $this->actingAs($outsider)
            ->put("/branch/commission-partners/{$partner->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertForbidden();

        $this->assertSame($originalHash, $partner->refresh()->password);
    }

    public function test_the_endpoint_only_works_on_actual_commission_partners(): void
    {
        // The route model is any User, so it must not become a way to reset an
        // arbitrary account belonging to this Branch Manager's chain.
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        [$member] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        // The VIP member was created by the partner, not the branch manager, so
        // ownership fails first — take one the branch manager does own instead.
        $impostor = User::factory()->create(['status' => 'active', 'created_by' => $branch->id]);
        $impostor->assignRole('vip_member');
        $originalHash = $impostor->password;

        $this->actingAs($branch)
            ->put("/branch/commission-partners/{$impostor->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertNotFound();

        $this->assertSame($originalHash, $impostor->refresh()->password);
        $this->assertNotNull($member);
    }

    public function test_a_commission_partner_cannot_reset_their_own_password_here(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $originalHash = $partner->password;

        $this->actingAs($partner)
            ->put("/branch/commission-partners/{$partner->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertForbidden();

        $this->assertSame($originalHash, $partner->refresh()->password);
    }
}
