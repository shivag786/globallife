<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PermissionMatrixService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Sub Admin sits between Super Admin and Branch Manager.
 *
 * The hierarchy is Super Admin → Sub Admin → Branch Manager → Commission Partner
 * → VIP Member, all on the one `created_by` chain. A Sub Admin granted the
 * `branch-managers` module creates and runs their own Branch Managers and sees
 * nobody else's; Super Admin sees every one, including those created before Sub
 * Admins existed (`created_by` NULL).
 */
class SubAdminHierarchyTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('super_admin');

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function managerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Branch Manager',
            'email' => 'new.bm@example.com',
            'password' => 'a-strong-password',
            'commission_percentage' => 30,
        ], $overrides);
    }

    /* ---------------------------------------------------------------- module */

    public function test_the_branch_managers_module_is_part_of_the_matrix(): void
    {
        $this->assertContains('branch-managers', PermissionMatrixService::MODULES);

        // Managing accounts has nothing to publish, approve or export, so those
        // toggles are not offered for this module.
        $this->assertSame(
            ['create', 'edit', 'delete'],
            PermissionMatrixService::actionsForModule('branch-managers'),
        );
        $this->assertContains('branch-managers.create', PermissionMatrixService::allPermissions());
        $this->assertNotContains('branch-managers.publish', PermissionMatrixService::allPermissions());
    }

    public function test_a_sub_admin_without_the_module_is_shut_out(): void
    {
        $subAdmin = $this->makeSubAdmin(['blog']);

        $this->actingAs($subAdmin)->get('/admin/branch-managers')->assertForbidden();
        $this->actingAs($subAdmin)->get('/admin/branch-managers/create')->assertForbidden();
        $this->actingAs($subAdmin)->post('/admin/branch-managers', $this->managerPayload())->assertForbidden();

        $this->assertSame(0, User::role('branch_manager')->count());
    }

    /* --------------------------------------------------------------- scoping */

    public function test_a_sub_admin_sees_only_the_branch_managers_they_own(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $mine = $this->makeBranchManager(30, $subAdmin);
        $someoneElses = $this->makeBranchManager(30, $this->makeSubAdmin(['branch-managers']));
        $legacy = $this->makeBranchManager(30);

        $response = $this->actingAs($subAdmin)->get('/admin/branch-managers');

        $response->assertOk();
        $response->assertSee($mine->email, false);
        $response->assertDontSee($someoneElses->email, false);
        $response->assertDontSee($legacy->email, false);
    }

    public function test_super_admin_sees_every_branch_manager_including_unowned_ones(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $owned = $this->makeBranchManager(30, $subAdmin);
        $legacy = $this->makeBranchManager(30);

        $response = $this->actingAs($this->superAdmin())->get('/admin/branch-managers');

        $response->assertOk();
        $response->assertSee($owned->email, false);
        $response->assertSee($legacy->email, false);
        // And who runs each one.
        $response->assertSee('Managed By', false);
        $response->assertSee($subAdmin->name, false);
    }

    public function test_a_sub_admin_cannot_open_or_change_someone_elses_branch_manager(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $outsider = $this->makeBranchManager(30, $this->makeSubAdmin(['branch-managers']));

        $this->actingAs($subAdmin)->get("/admin/branch-managers/{$outsider->id}/edit")->assertForbidden();

        $this->actingAs($subAdmin)
            ->put("/admin/branch-managers/{$outsider->id}", $this->managerPayload([
                'name' => 'Hijacked',
                'email' => $outsider->email,
            ]))
            ->assertForbidden();

        $this->assertNotSame('Hijacked', $outsider->refresh()->name);
    }

    public function test_a_sub_admin_cannot_reset_someone_elses_branch_managers_password(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $outsider = $this->makeBranchManager(30, $this->makeSubAdmin(['branch-managers']));
        $originalHash = $outsider->password;

        $this->actingAs($subAdmin)
            ->put("/admin/branch-managers/{$outsider->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertForbidden();

        $this->assertSame($originalHash, $outsider->refresh()->password);
    }

    public function test_a_sub_admin_cannot_suspend_someone_elses_branch_manager(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $outsider = $this->makeBranchManager(30, $this->makeSubAdmin(['branch-managers']));

        $this->actingAs($subAdmin)
            ->patch("/admin/branch-managers/{$outsider->id}/toggle-status")
            ->assertForbidden();

        $this->assertSame('active', $outsider->refresh()->status);
    }

    public function test_a_sub_admin_cannot_touch_a_legacy_branch_manager_nobody_owns(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $legacy = $this->makeBranchManager(30);

        $this->actingAs($subAdmin)->get("/admin/branch-managers/{$legacy->id}/edit")->assertForbidden();
    }

    public function test_the_endpoint_only_works_on_actual_branch_managers(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        // A commission partner they somehow created: not a Branch Manager, so the
        // route model being any User must not become a way in.
        $impostor = User::factory()->create(['status' => 'active', 'created_by' => $subAdmin->id]);
        $impostor->assignRole('commission_partner');

        $this->actingAs($subAdmin)->get("/admin/branch-managers/{$impostor->id}/edit")->assertNotFound();
    }

    /* -------------------------------------------------------------- creating */

    public function test_a_sub_admins_new_branch_manager_belongs_to_them(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);

        $this->actingAs($subAdmin)
            ->post('/admin/branch-managers', $this->managerPayload())
            ->assertRedirect(route('admin.branch-managers.index'));

        $manager = User::where('email', 'new.bm@example.com')->sole();

        $this->assertTrue($manager->hasRole('branch_manager'));
        $this->assertSame($subAdmin->id, $manager->created_by);
        $this->assertTrue($subAdmin->branchManagers->contains('id', $manager->id));
    }

    public function test_a_sub_admin_cannot_park_a_branch_manager_under_someone_else(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $other = $this->makeSubAdmin(['branch-managers']);

        // Forging the owner field is ignored — ownership follows who created it.
        $this->actingAs($subAdmin)
            ->post('/admin/branch-managers', $this->managerPayload(['created_by' => $other->id]))
            ->assertRedirect();

        $this->assertSame($subAdmin->id, User::where('email', 'new.bm@example.com')->sole()->created_by);
    }

    public function test_the_owner_field_is_shown_to_super_admin_only(): void
    {
        $this->makeSubAdmin(['branch-managers']);

        $this->actingAs($this->superAdmin())->get('/admin/branch-managers/create')
            ->assertOk()
            ->assertSee('Managed By', false)
            ->assertSee('name="created_by"', false);

        $this->actingAs($this->makeSubAdmin(['branch-managers']))->get('/admin/branch-managers/create')
            ->assertOk()
            ->assertDontSee('name="created_by"', false);
    }

    public function test_super_admin_can_hand_a_branch_manager_to_a_sub_admin(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);

        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->managerPayload(['created_by' => $subAdmin->id]))
            ->assertRedirect();

        $this->assertSame($subAdmin->id, User::where('email', 'new.bm@example.com')->sole()->created_by);
    }

    public function test_super_admin_can_reassign_an_existing_branch_manager(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $legacy = $this->makeBranchManager(30);

        $this->actingAs($this->superAdmin())
            ->put("/admin/branch-managers/{$legacy->id}", $this->managerPayload([
                'email' => $legacy->email,
                'created_by' => $subAdmin->id,
            ]))
            ->assertRedirect();

        $this->assertSame($subAdmin->id, $legacy->refresh()->created_by);

        // And now the Sub Admin can see it.
        $this->actingAs($subAdmin)->get('/admin/branch-managers')
            ->assertOk()
            ->assertSee($legacy->email, false);
    }

    public function test_reassigning_back_to_none_returns_them_to_super_admin(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $manager = $this->makeBranchManager(30, $subAdmin);

        $this->actingAs($this->superAdmin())
            ->put("/admin/branch-managers/{$manager->id}", $this->managerPayload([
                'email' => $manager->email,
                'created_by' => '',
            ]))
            ->assertRedirect();

        $this->assertNull($manager->refresh()->created_by);
        $this->actingAs($subAdmin)->get('/admin/branch-managers')
            ->assertOk()
            ->assertDontSee($manager->email, false);
    }

    public function test_a_sub_admin_editing_their_own_manager_cannot_change_the_owner(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $other = $this->makeSubAdmin(['branch-managers']);
        $manager = $this->makeBranchManager(30, $subAdmin);

        $this->actingAs($subAdmin)
            ->put("/admin/branch-managers/{$manager->id}", $this->managerPayload([
                'name' => 'Renamed',
                'email' => $manager->email,
                'created_by' => $other->id,
            ]))
            ->assertRedirect();

        $manager->refresh();

        $this->assertSame('Renamed', $manager->name, 'the ordinary edit still goes through');
        $this->assertSame($subAdmin->id, $manager->created_by, 'but ownership did not move');
    }

    /* ------------------------------------------------------- sub admin crud */

    public function test_only_super_admin_manages_sub_admins(): void
    {
        $this->actingAs($this->makeSubAdmin(['branch-managers']))->get('/admin/sub-admins')->assertForbidden();
        $this->actingAs($this->superAdmin())->get('/admin/sub-admins')->assertOk();
    }

    public function test_a_sub_admin_cannot_mint_another_sub_admin(): void
    {
        $this->actingAs($this->makeSubAdmin(['branch-managers']))
            ->post('/admin/sub-admins', [
                'name' => 'Sneaky',
                'email' => 'sneaky@example.com',
                'password' => 'a-strong-password',
            ])
            ->assertForbidden();

        $this->assertSame(1, User::role('sub_admin')->count());
    }

    public function test_super_admin_creates_a_sub_admin_and_lands_on_its_permissions(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/sub-admins', [
                'name' => 'Regional Head',
                'email' => 'regional@example.com',
                'mobile' => '9876543210',
                'password' => 'a-strong-password',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $subAdmin = User::where('email', 'regional@example.com')->sole();

        $this->assertTrue($subAdmin->hasRole('sub_admin'));
        $this->assertSame('9876543210', $subAdmin->mobile);
        $this->assertSame('active', $subAdmin->status);
        // Starts with nothing granted — access is chosen deliberately.
        $this->assertCount(0, $subAdmin->getAllPermissions());
    }

    public function test_granting_the_module_opens_the_screen(): void
    {
        $subAdmin = $this->makeSubAdmin();

        $this->actingAs($subAdmin)->get('/admin/branch-managers')->assertForbidden();

        $this->actingAs($this->superAdmin())
            ->put("/admin/sub-admins/{$subAdmin->id}/permissions", [
                'permissions' => ['branch-managers.create' => '1', 'branch-managers.edit' => '1'],
            ])
            ->assertRedirect();

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($subAdmin->fresh())->get('/admin/branch-managers')->assertOk();
    }

    public function test_a_permission_outside_the_matrix_is_discarded(): void
    {
        $subAdmin = $this->makeSubAdmin();

        $this->actingAs($this->superAdmin())
            ->put("/admin/sub-admins/{$subAdmin->id}/permissions", [
                'permissions' => [
                    'branch-managers.create' => '1',
                    // Not on offer for this module, and not a real permission.
                    'branch-managers.publish' => '1',
                    'settings.destroy-everything' => '1',
                ],
            ])
            ->assertRedirect();

        $held = $subAdmin->fresh()->getAllPermissions()->pluck('name')->all();

        $this->assertSame(['branch-managers.create'], $held);
    }

    public function test_the_sub_admin_password_reset_signs_them_out_everywhere(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $subAdmin->forceFill(['remember_token' => 'old-remember-token'])->save();

        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'sa-session-1',
            'user_id' => $subAdmin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'x',
            'last_activity' => time(),
        ]);

        $this->actingAs($this->superAdmin())
            ->put("/admin/sub-admins/{$subAdmin->id}/password", [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertRedirect(route('admin.sub-admins.edit', $subAdmin));

        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $subAdmin->id)->count());
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('a-brand-new-password', $subAdmin->refresh()->password));
        $this->assertNotSame('old-remember-token', $subAdmin->remember_token);
    }

    public function test_suspending_a_sub_admin_keeps_them_out(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);

        $this->actingAs($this->superAdmin())
            ->patch("/admin/sub-admins/{$subAdmin->id}/toggle-status")
            ->assertRedirect();

        $this->assertSame('blocked', $subAdmin->refresh()->status);

        // EnsureAccountIsActive logs out anyone not active.
        $this->actingAs($subAdmin)->get('/admin/dashboard')->assertRedirect();
    }

    public function test_the_sub_admin_endpoints_refuse_a_user_who_is_not_one(): void
    {
        $notASubAdmin = $this->makeBranchManager(30);
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)->get("/admin/sub-admins/{$notASubAdmin->id}/edit")->assertNotFound();
        $this->actingAs($superAdmin)->get("/admin/sub-admins/{$notASubAdmin->id}/permissions")->assertNotFound();
        $this->actingAs($superAdmin)->patch("/admin/sub-admins/{$notASubAdmin->id}/toggle-status")->assertNotFound();
    }

    /* ----------------------------------------------------------- navigation */

    public function test_the_sidebar_offers_each_role_its_own_entries(): void
    {
        $superAdmin = $this->superAdmin();
        $withModule = $this->makeSubAdmin(['branch-managers']);
        $withoutModule = $this->makeSubAdmin(['blog']);

        $this->actingAs($superAdmin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Sub Admins', false)
            ->assertSee(route('admin.sub-admins.index'), false);

        $this->actingAs($withModule)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee(route('admin.branch-managers.index'), false)
            // Managing Sub Admins is not theirs.
            ->assertDontSee(route('admin.sub-admins.index'), false);

        $this->actingAs($withoutModule)->get('/admin/dashboard')
            ->assertOk()
            ->assertDontSee(route('admin.branch-managers.index'), false);
    }

    public function test_a_sub_admin_dashboard_counts_only_their_own_network(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $mine = $this->makeBranchManager(30, $subAdmin);
        $this->makeCommissionPartner($mine, 25);
        $this->makeCommissionPartner($mine, 25);

        // Someone else's chain must not be counted.
        $theirs = $this->makeBranchManager(30, $this->makeSubAdmin(['branch-managers']));
        $this->makeCommissionPartner($theirs, 25);

        $response = $this->actingAs($subAdmin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('My Network', false);
        $response->assertViewHas('network', fn (array $network) => $network['branch_managers'] === 1
            && $network['active'] === 1
            && $network['commission_partners'] === 2);
    }

    public function test_the_commission_split_still_walks_past_the_sub_admin(): void
    {
        // A Sub Admin is a management layer, not a beneficiary: inserting one
        // above a Branch Manager must not change anybody's commission.
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $branch = $this->makeBranchManager(30, $subAdmin);
        $partner = $this->makeCommissionPartner($branch, 25);
        [, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        $transaction = app(\App\Services\VipActivationService::class)->activate($microsite, $partner);

        $this->assertEquals(250.0, (float) $transaction->commission_partner_amount);
        $this->assertEquals(50.0, (float) $transaction->branch_manager_amount);
        $this->assertEquals(700.0, (float) $transaction->company_amount, 'the Sub Admin takes no cut');
        $this->assertSame($branch->id, $transaction->branch_manager_id);
    }
}
