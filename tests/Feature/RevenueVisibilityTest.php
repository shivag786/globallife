<?php

namespace Tests\Feature;

use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

class RevenueVisibilityTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_a_partner_only_sees_their_own_activations(): void
    {
        $branch = $this->makeBranchManager(30);
        $partnerA = $this->makeCommissionPartner($branch, 25);
        $partnerB = $this->makeCommissionPartner($branch, 25);

        [, $micrositeA] = $this->makeVipMember($partnerA, $this->makePlan(1000), $this->makeCity());
        [, $micrositeB] = $this->makeVipMember($partnerB, $this->makePlan(1000), $this->makeCity());

        $service = app(VipActivationService::class);
        $service->activate($micrositeA, $partnerA);
        $service->activate($micrositeB, $partnerB);

        $response = $this->actingAs($partnerA)->get('/manager/revenue');

        $response->assertOk();
        $response->assertSee($micrositeA->business_name);
        $response->assertDontSee($micrositeB->business_name);
    }

    public function test_branch_manager_sees_all_partners_activations(): void
    {
        $branch = $this->makeBranchManager(30);
        $partnerA = $this->makeCommissionPartner($branch, 25);
        $partnerB = $this->makeCommissionPartner($branch, 25);

        [, $micrositeA] = $this->makeVipMember($partnerA, $this->makePlan(1000), $this->makeCity());
        [, $micrositeB] = $this->makeVipMember($partnerB, $this->makePlan(1000), $this->makeCity());

        $service = app(VipActivationService::class);
        $service->activate($micrositeA, $partnerA);
        $service->activate($micrositeB, $partnerB);

        $response = $this->actingAs($branch)->get('/branch/revenue');

        $response->assertOk();
        $response->assertSee($micrositeA->business_name);
        $response->assertSee($micrositeB->business_name);
    }
}
