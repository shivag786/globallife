<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

class ProductCommissionTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private function service(): ProductCommissionService
    {
        return app(ProductCommissionService::class);
    }

    private function makeProduct(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'short_description' => 'A test product.',
            'price' => 1000,
            'status' => 'active',
        ], $attrs));
    }

    private function rule(string $scope, int $scopeId, string $role, string $type, float $value): void
    {
        CommissionRule::create([
            'scope' => $scope,
            'scope_id' => $scopeId,
            'role' => $role,
            'type' => $type,
            'value' => $value,
            'status' => 'active',
        ]);
    }

    public function test_global_percentage_split_pays_each_role_and_company_gets_remainder(): void
    {
        $this->seedRoles();
        $bm = $this->makeBranchManager(30);
        $cp = $this->makeCommissionPartner($bm, 25);
        [$vip] = $this->makeVipMember($cp, $this->makePlan(999), $this->makeCity());

        $product = $this->makeProduct(['price' => 1000]);
        $this->rule('global', 0, 'vip_member', 'percent', 10);
        $this->rule('global', 0, 'commission_partner', 'percent', 7);
        $this->rule('global', 0, 'branch_manager', 'percent', 5);

        $split = $this->service()->calculateSplit($product, $vip, 1000);
        $byRole = collect($split['lines'])->keyBy('role');

        $this->assertEqualsWithDelta(100, $byRole['vip_member']['amount'], 0.001);
        $this->assertEqualsWithDelta(70, $byRole['commission_partner']['amount'], 0.001);
        $this->assertEqualsWithDelta(50, $byRole['branch_manager']['amount'], 0.001);
        $this->assertEqualsWithDelta(780, $split['company'], 0.001);

        // Beneficiaries map to the correct users in the chain.
        $this->assertSame($vip->id, $byRole['vip_member']['user_id']);
        $this->assertSame($cp->id, $byRole['commission_partner']['user_id']);
        $this->assertSame($bm->id, $byRole['branch_manager']['user_id']);

        // The split always reconciles exactly to the base.
        $this->assertEqualsWithDelta(1000, $split['company'] + collect($split['lines'])->sum('amount'), 0.001);
    }

    public function test_product_rule_overrides_category_which_overrides_global(): void
    {
        $this->seedRoles();
        $category = Category::create(['name' => 'Wellness', 'slug' => 'wellness-'.uniqid(), 'status' => 'active']);
        $product = $this->makeProduct(['category_id' => $category->id]);

        $this->rule('global', 0, 'vip_member', 'percent', 10);
        $this->rule('category', $category->id, 'vip_member', 'percent', 15);
        $this->rule('product', $product->id, 'vip_member', 'percent', 20);

        $resolved = $this->service()->resolveRule($product, 'vip_member');
        $this->assertSame('product', $resolved->scope);
        $this->assertEqualsWithDelta(20, (float) $resolved->value, 0.001);

        // Remove the product rule → falls back to category.
        CommissionRule::where('scope', 'product')->delete();
        $this->assertSame('category', $this->service()->resolveRule($product, 'vip_member')->scope);

        // Remove the category rule → falls back to global.
        CommissionRule::where('scope', 'category')->delete();
        $this->assertSame('global', $this->service()->resolveRule($product, 'vip_member')->scope);
    }

    public function test_missing_branch_manager_folds_into_company_share(): void
    {
        $this->seedRoles();
        // A Commission Partner with no Branch Manager above them.
        $cp = User::factory()->create(['status' => 'active']);
        $cp->assignRole('commission_partner');
        [$vip] = $this->makeVipMember($cp, $this->makePlan(999), $this->makeCity());

        $product = $this->makeProduct(['price' => 1000]);
        $this->rule('global', 0, 'vip_member', 'percent', 10);
        $this->rule('global', 0, 'commission_partner', 'percent', 7);
        $this->rule('global', 0, 'branch_manager', 'percent', 5);

        $split = $this->service()->calculateSplit($product, $vip, 1000);
        $roles = collect($split['lines'])->pluck('role');

        $this->assertTrue($roles->contains('vip_member'));
        $this->assertTrue($roles->contains('commission_partner'));
        $this->assertFalse($roles->contains('branch_manager'), 'No Branch Manager in the chain.');

        // The Branch Manager's unassigned 5% (₹50) folds into the company share.
        $this->assertEqualsWithDelta(830, $split['company'], 0.001);
    }

    public function test_fixed_amounts_and_values_are_clamped_to_the_base(): void
    {
        $service = $this->service();

        // Fixed ₹600 on a ₹500 sale is capped at ₹500.
        $fixed = new CommissionRule(['type' => 'fixed', 'value' => 600]);
        $this->assertEqualsWithDelta(500, $service->ruleAmount($fixed, 500), 0.001);

        // Percentage is straightforward.
        $percent = new CommissionRule(['type' => 'percent', 'value' => 10]);
        $this->assertEqualsWithDelta(50, $service->ruleAmount($percent, 500), 0.001);

        // A percentage above 100 is clamped so no one earns more than the sale.
        $overload = new CommissionRule(['type' => 'percent', 'value' => 150]);
        $this->assertEqualsWithDelta(500, $service->ruleAmount($overload, 500), 0.001);
    }
}
