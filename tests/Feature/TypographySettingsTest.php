<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingsService;
use App\Services\TypographyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Fonts are chosen in Site Settings and printed into every layout; dashboards
 * no longer advertise "coming soon" menus.
 */
class TypographySettingsTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedRoles();
    }

    private function root(): User
    {
        Role::findOrCreate('super_admin', 'web');
        $user = User::create([
            'name' => 'Root', 'email' => 'root'.uniqid().'@example.com',
            'password' => bcrypt('secret1234'), 'status' => 'active',
        ]);
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_defaults_apply_when_nothing_is_saved(): void
    {
        $typography = app(TypographyService::class);

        $this->assertSame([
            'body' => 'Instrument Sans', 'heading' => 'Manrope',
            'display' => 'Playfair Display', 'size' => 16,
        ], $typography->current());
        $this->assertStringContainsString('family=Playfair+Display:wght@600;700', $typography->googleFontsUrl());
        $this->assertStringContainsString('--bs-body-font-size:1rem', $typography->cssVariables());
    }

    public function test_super_admin_can_change_fonts_and_every_layout_uses_them(): void
    {
        $admin = $this->root();

        $this->actingAs($admin)->put('/admin/settings', [
            'font_body' => 'Poppins',
            'font_heading' => 'Montserrat',
            'font_display' => 'Lora',
            'font_size_base' => 15,
        ])->assertRedirect('/admin/settings');

        $this->assertSame('Poppins', app(SettingsService::class)->get('font_body'));

        // Dashboard layout
        $this->actingAs($admin)->get('/admin/settings')
            ->assertOk()
            ->assertSee('family=Poppins', false)
            ->assertSee('family=Montserrat', false)
            ->assertSee("--font-display:'Lora', ui-serif", false)
            ->assertSee('--bs-body-font-size:0.9375rem', false);

        // Public layout
        auth()->logout();
        $this->get('/')->assertOk()->assertSee("--bs-font-sans-serif:'Poppins'", false);
    }

    public function test_fonts_outside_the_curated_list_are_rejected(): void
    {
        $this->actingAs($this->root())->put('/admin/settings', [
            'font_body' => "Evil'};body{display:none",
            'font_size_base' => 40,
        ])->assertSessionHasErrors(['font_body', 'font_size_base']);

        $this->assertNull(app(SettingsService::class)->get('font_body'));
    }

    public function test_vip_dashboard_shows_no_coming_soon_menus(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        $this->actingAs($member)->get('/vip/dashboard')
            ->assertOk()
            ->assertDontSee('Phase 2', false)
            ->assertDontSee('>soon<', false)
            ->assertDontSee('Offers &amp; Coupons', false);
    }
}
