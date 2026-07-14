<?php

namespace Tests\Feature;

use App\Models\CommissionTransaction;
use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

class CommissionSplitTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_it_splits_the_joining_price_three_ways(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        [, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        $txn = app(VipActivationService::class)->activate($microsite, $partner);

        // Partner 25% = 250, Branch keeps unused 5% = 50, Company gets remaining 70% = 700.
        $this->assertEqualsWithDelta(250.00, (float) $txn->commission_partner_amount, 0.001);
        $this->assertEqualsWithDelta(50.00, (float) $txn->branch_manager_amount, 0.001);
        $this->assertEqualsWithDelta(700.00, (float) $txn->company_amount, 0.001);

        // The three shares always reconcile to the exact package amount.
        $sum = (float) $txn->commission_partner_amount + (float) $txn->branch_manager_amount + (float) $txn->company_amount;
        $this->assertEqualsWithDelta(1000.00, $sum, 0.001);

        $this->assertNotNull($microsite->fresh()->activated_at);
        $this->assertSame($partner->id, $txn->commission_partner_id);
        $this->assertSame($branch->id, $txn->branch_manager_id);
    }

    public function test_branch_share_clamps_to_zero_when_partner_meets_or_exceeds_cap(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 40); // above the branch cap
        [, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        $txn = app(VipActivationService::class)->activate($microsite, $partner);

        $this->assertEqualsWithDelta(400.00, (float) $txn->commission_partner_amount, 0.001);
        $this->assertEqualsWithDelta(0.00, (float) $txn->branch_manager_amount, 0.001);
        $this->assertEqualsWithDelta(600.00, (float) $txn->company_amount, 0.001);
    }

    public function test_it_cannot_be_activated_twice_and_records_no_duplicate(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        [, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        $service = app(VipActivationService::class);
        $service->activate($microsite, $partner);

        try {
            $service->activate($microsite->fresh(), $partner);
            $this->fail('Expected a RuntimeException on second activation.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('already been activated', $e->getMessage());
        }

        $this->assertSame(1, CommissionTransaction::where('vip_microsite_id', $microsite->id)->count());
    }
}
