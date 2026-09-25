<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\User;
use App\Services\BranchPermissionMatrixService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Admin form behaviour: the permission grid only offers modules that exist and
 * can be filled in bulk, and a Branch Manager no longer has to be given cities
 * up front.
 */
class AdminFormUsabilityTest extends TestCase
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

    private function city(string $name): City
    {
        return City::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'state' => 'Test State',
            'status' => 'active',
        ]);
    }

    public function test_the_permission_grid_lists_only_modules_that_have_a_page(): void
    {
        $manager = $this->makeBranchManager(30);

        $response = $this->actingAs($this->superAdmin())
            ->get("/admin/branch-managers/{$manager->id}/permissions");

        $response->assertOk();
        $response->assertSee('commission partners', false);

        // Phase 2 placeholders have no route behind them, so they are not offered.
        foreach (['sales-tracking', 'discount-management', 'customer-management', 'vip-enquiries'] as $inactive) {
            $response->assertDontSee($inactive, false);
            $response->assertDontSee(str_replace('-', ' ', $inactive), false);
        }
    }

    public function test_the_grid_offers_bulk_toggles_rather_than_only_single_boxes(): void
    {
        $manager = $this->makeBranchManager(30);

        $response = $this->actingAs($this->superAdmin())
            ->get("/admin/branch-managers/{$manager->id}/permissions");

        $response->assertOk();
        $response->assertSee('data-permission-matrix', false);
        $response->assertSee('data-permission-all', false);
        $response->assertSee('data-permission-row', false);
        $response->assertSee('data-permission-column', false);
        // The real checkboxes are still there and still post the same names.
        $response->assertSee('permissions[branch.commission-partners.view]', false);
    }

    public function test_the_active_module_list_is_a_subset_of_every_known_module(): void
    {
        $active = BranchPermissionMatrixService::activeModules();

        $this->assertNotEmpty($active);
        foreach ($active as $module) {
            $this->assertContains($module, BranchPermissionMatrixService::MODULES);
        }

        // Every permission the screen offers is still a recognised name.
        foreach (BranchPermissionMatrixService::activePermissions() as $permission) {
            $this->assertContains($permission, BranchPermissionMatrixService::allPermissions());
        }
    }

    public function test_the_branch_sidebar_shows_no_phase_two_placeholders(): void
    {
        $manager = $this->makeBranchManager(30);
        $manager->givePermissionTo(BranchPermissionMatrixService::allPermissions());

        $response = $this->actingAs($manager)->get('/branch/dashboard');

        $response->assertOk();
        $response->assertSee('Commission Partners', false);
        $response->assertDontSee('soon', false);
        $response->assertDontSee('Sales Tracking', false);
        $response->assertDontSee('Discount Management', false);
    }

    public function test_a_branch_manager_can_be_created_with_no_cities(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/branch-managers', [
            'name' => 'Territoryless Manager',
            'email' => 'territoryless@example.com',
            'password' => 'a-strong-password',
            'commission_percentage' => 30,
            // cities deliberately absent
        ])->assertRedirect(route('admin.branch-managers.index'));

        $manager = User::where('email', 'territoryless@example.com')->sole();

        $this->assertTrue($manager->hasRole('branch_manager'));
        $this->assertSame(0, $manager->branchCities()->count());
    }

    public function test_cities_can_be_cleared_on_edit(): void
    {
        $manager = $this->makeBranchManager(30);
        $manager->branchCities()->sync([$this->city('Jhansi')->id]);
        $this->assertSame(1, $manager->branchCities()->count());

        $this->actingAs($this->superAdmin())->put("/admin/branch-managers/{$manager->id}", [
            'name' => $manager->name,
            'email' => $manager->email,
            'commission_percentage' => 30,
        ])->assertRedirect(route('admin.branch-managers.index'));

        $this->assertSame(0, $manager->fresh()->branchCities()->count());
    }

    public function test_cities_still_have_to_be_real_when_given(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/branch-managers', [
            'name' => 'Bad Cities',
            'email' => 'bad.cities@example.com',
            'password' => 'a-strong-password',
            'commission_percentage' => 30,
            'cities' => [999999],
        ])->assertSessionHasErrors('cities.0');

        $this->assertSame(0, User::where('email', 'bad.cities@example.com')->count());
    }
}
