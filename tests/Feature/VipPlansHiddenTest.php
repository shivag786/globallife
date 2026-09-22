<?php

namespace Tests\Feature;

use App\Models\HomeSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * "Become a VIP" and "VIP Plans" are hidden from the public site: no nav entry,
 * no header CTA, no footer link, no chatbot quick reply, and no homepage plans
 * section. Admin plan management is untouched.
 */
class VipPlansHiddenTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    /**
     * @return array<int, string>
     */
    public static function publicPages(): array
    {
        return [['/'], ['/products'], ['/blog'], ['/events'], ['/contact']];
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_show_no_vip_plan_entry_points(string $url): void
    {
        $response = $this->get($url);

        $response->assertOk();
        $response->assertDontSee('Become a VIP', false);
        $response->assertDontSee('VIP Plans', false);
        // The link itself must be gone, not merely relabelled.
        $response->assertDontSee('href="'.route('vip-plans.index').'"', false);
    }

    public function test_the_homepage_does_not_render_an_active_vip_plans_section(): void
    {
        $plan = $this->makePlan(4999);

        HomeSection::create([
            'type' => 'vip_plans',
            'title' => 'Pick Your VIP Package',
            'status' => 'active',
            'display_order' => 1,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Pick Your VIP Package', false);
        $response->assertDontSee($plan->name, false);
    }

    public function test_other_active_home_sections_still_render(): void
    {
        HomeSection::create([
            'type' => 'about',
            'title' => 'About Global Life',
            'status' => 'active',
            'display_order' => 1,
        ]);

        $this->get('/')->assertOk()->assertSee('About Global Life', false);
    }

    // The admin VIP Plans CRUD used to be asserted here, to prove that hiding the
    // public entry points had not taken the admin tooling with it. That screen has
    // since been removed on purpose — the four packages are fixed — so the
    // contract now lives in LegacyVipPlansRemovedTest, which asserts it is gone.
}
