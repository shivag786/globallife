<?php

namespace Tests\Feature;

use App\Models\BusinessReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

class ReviewModerationTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_owner_can_approve_a_pending_review(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        $review = BusinessReview::create([
            'vip_microsite_id' => $microsite->id,
            'customer_name' => 'Visitor',
            'rating' => 5,
            'review_text' => 'Great service',
            'status' => 'pending',
        ]);

        $this->actingAs($member)->patch("/vip/reviews/{$review->id}/approve")->assertRedirect();

        $this->assertSame('approved', $review->fresh()->status);
    }

    public function test_a_different_vip_member_cannot_moderate_someone_elses_review(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);

        [, $micrositeA] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());
        [$memberB] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        $review = BusinessReview::create([
            'vip_microsite_id' => $micrositeA->id,
            'customer_name' => 'Visitor',
            'rating' => 4,
            'status' => 'pending',
        ]);

        $this->actingAs($memberB)->patch("/vip/reviews/{$review->id}/approve")->assertForbidden();

        $this->assertSame('pending', $review->fresh()->status);
    }
}
