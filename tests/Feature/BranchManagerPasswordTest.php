<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * A Super Admin setting a Branch Manager's password from the Branch Manager edit
 * screen — the only place in the app where one account may change another's.
 *
 * A reset is normally a response to a lost or compromised login, so it also has
 * to cut off the old credentials: stored sessions are dropped and the
 * remember-me token rotated.
 */
class BranchManagerPasswordTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function superAdmin(): User
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('super_admin');

        return $admin;
    }

    public function test_the_change_password_form_is_on_the_branch_manager_edit_screen(): void
    {
        $manager = $this->makeBranchManager(30);

        $response = $this->actingAs($this->superAdmin())
            ->get("/admin/branch-managers/{$manager->id}/edit");

        $response->assertOk();
        $response->assertSee('Change Password', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
        $response->assertSee(route('admin.branch-managers.password.update', $manager), false);
    }

    public function test_the_create_screen_has_no_change_password_form(): void
    {
        // Creating already takes a password; this is specifically a *change* form
        // and must not appear anywhere but edit.
        $response = $this->actingAs($this->superAdmin())->get('/admin/branch-managers/create');

        $response->assertOk();
        $response->assertDontSee('Change Password', false);
        $response->assertDontSee('password_confirmation', false);
    }

    public function test_the_branch_manager_list_offers_no_password_control(): void
    {
        $this->makeBranchManager(30);

        $this->actingAs($this->superAdmin())->get('/admin/branch-managers')
            ->assertOk()
            ->assertDontSee('Change Password', false)
            ->assertDontSee('/password', false);
    }

    public function test_a_super_admin_can_set_a_new_password(): void
    {
        $manager = $this->makeBranchManager(30);
        $originalHash = $manager->password;

        $this->actingAs($this->superAdmin())
            ->put("/admin/branch-managers/{$manager->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertRedirect(route('admin.branch-managers.edit', $manager))
            ->assertSessionHas('status');

        $manager->refresh();

        $this->assertTrue(Hash::check('a-brand-new-password', $manager->password));
        $this->assertNotSame($originalHash, $manager->password);
    }

    public function test_the_reset_signs_them_out_everywhere(): void
    {
        $manager = $this->makeBranchManager(30);
        $other = $this->makeBranchManager(30);

        $manager->forceFill(['remember_token' => 'old-remember-token'])->save();

        foreach ([$manager->id, $manager->id, $other->id] as $i => $userId) {
            DB::table('sessions')->insert([
                'id' => 'session-'.$i,
                'user_id' => $userId,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'x',
                'last_activity' => time(),
            ]);
        }

        $this->actingAs($this->superAdmin())
            ->put("/admin/branch-managers/{$manager->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ]);

        // Their live logins are gone; nobody else's are touched.
        $this->assertSame(0, DB::table('sessions')->where('user_id', $manager->id)->count());
        $this->assertSame(1, DB::table('sessions')->where('user_id', $other->id)->count());

        // A remember-me cookie issued against the old token no longer works.
        $this->assertNotSame('old-remember-token', $manager->refresh()->remember_token);
    }

    public function test_the_two_passwords_have_to_match(): void
    {
        $manager = $this->makeBranchManager(30);
        $originalHash = $manager->password;

        $this->actingAs($this->superAdmin())
            ->put("/admin/branch-managers/{$manager->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-different-password',
            ])
            ->assertSessionHasErrors('password');

        $this->assertSame($originalHash, $manager->refresh()->password);
    }

    public function test_a_short_password_is_refused(): void
    {
        $manager = $this->makeBranchManager(30);
        $originalHash = $manager->password;

        $this->actingAs($this->superAdmin())
            ->put("/admin/branch-managers/{$manager->id}/password", [
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');

        $this->assertSame($originalHash, $manager->refresh()->password);
    }

    public function test_the_endpoint_only_works_on_actual_branch_managers(): void
    {
        // The route model is any User, so it must not become a way to reset an
        // arbitrary account's password.
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $originalHash = $partner->password;

        $this->actingAs($this->superAdmin())
            ->put("/admin/branch-managers/{$partner->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertNotFound();

        $this->assertSame($originalHash, $partner->refresh()->password);
    }

    public function test_nobody_below_super_admin_can_reset_a_password(): void
    {
        $manager = $this->makeBranchManager(30);
        $originalHash = $manager->password;

        $payload = [
            'password' => 'a-brand-new-password',
            'password_confirmation' => 'a-brand-new-password',
        ];

        foreach (['admin', 'sub_admin'] as $role) {
            $staff = User::factory()->create(['status' => 'active']);
            $staff->assignRole($role);

            $this->actingAs($staff)
                ->put("/admin/branch-managers/{$manager->id}/password", $payload)
                ->assertForbidden();
        }

        // And the Branch Manager cannot reset their own this way either.
        $this->actingAs($manager)
            ->put("/admin/branch-managers/{$manager->id}/password", $payload)
            ->assertForbidden();

        $this->assertSame($originalHash, $manager->refresh()->password);
    }
}
