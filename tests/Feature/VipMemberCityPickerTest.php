<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\User;
use App\Models\VipPlan;
use App\Services\CityDirectoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The state → city picker now lives on the Commission Partner's "add VIP Member"
 * form, not on the Branch Manager's partner form.
 *
 * Territory follows the work: a partner picks up the city they register a member
 * in, and their Branch Manager picks up the matching branch — so neither has to
 * be handed cities on a form up front.
 */
class VipMemberCityPickerTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function city(string $name, string $state): City
    {
        return City::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'state' => $state,
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Member',
            'email' => 'new.member@example.com',
            'password' => 'a-strong-password',
            'vip_plan_id' => VipPlan::where('slug', 'growth')->value('id'),
            'business_name' => 'Lifeline Clinic',
        ], $overrides);
    }

    private function member(): ?User
    {
        return User::where('email', 'new.member@example.com')->first();
    }

    public function test_the_add_member_form_offers_the_state_to_city_cascade(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $jhansi = $this->city('Jhansi', 'Uttar Pradesh');
        $indore = $this->city('Indore', 'Madhya Pradesh');

        $response = $this->actingAs($partner)->get('/manager/vip-members/create');

        $response->assertOk();
        $response->assertSee('data-city-picker="single"', false);
        $response->assertSee('Uttar Pradesh', false);
        $response->assertSee('Madhya Pradesh', false);
        $response->assertSee('Jhansi', false);
        $response->assertSee('Indore', false);
        $response->assertSee((string) $jhansi->id, false);
        $response->assertSee((string) $indore->id, false);
        // The escape hatch for a city that is not listed.
        $response->assertSee('__other__', false);
        $response->assertSee('new_city[name]', false);
    }

    public function test_choosing_an_existing_city_registers_the_member_there(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        $jhansi = $this->city('Jhansi', 'Uttar Pradesh');

        $this->actingAs($partner)
            ->post('/manager/vip-members', $this->payload(['city_id' => $jhansi->id]))
            ->assertRedirect(route('manager.vip-members.index'));

        $microsite = $this->member()->vipMicrosite;
        $this->assertSame($jhansi->id, $microsite->city_id);

        // Territory follows the work, at both levels.
        $this->assertContains($jhansi->id, $partner->cities()->pluck('cities.id')->all());
        $this->assertContains($jhansi->id, $branch->branchCities()->pluck('cities.id')->all());
    }

    public function test_a_typed_city_is_created_and_picked_up_as_territory(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload([
            'new_city' => ['name' => 'Orchha', 'state' => 'Madhya Pradesh'],
        ]))->assertRedirect();

        $orchha = City::where('name', 'Orchha')->sole();
        $this->assertSame('Madhya Pradesh', $orchha->state);
        $this->assertSame('orchha', $orchha->slug);

        $this->assertSame($orchha->id, $this->member()->vipMicrosite->city_id);
        $this->assertContains($orchha->id, $partner->cities()->pluck('cities.id')->all());
        $this->assertContains($orchha->id, $branch->branchCities()->pluck('cities.id')->all());
    }

    public function test_typing_a_city_that_exists_reuses_it_instead_of_duplicating(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $existing = $this->city('Jhansi', 'Uttar Pradesh');

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload([
            // Different casing and padding — still the same city.
            'new_city' => ['name' => '  jhansi ', 'state' => 'uttar pradesh'],
        ]))->assertRedirect();

        $this->assertSame(1, City::where('state', 'Uttar Pradesh')->count());
        $this->assertSame($existing->id, $this->member()->vipMicrosite->city_id);
    }

    public function test_a_city_has_to_be_chosen_or_typed(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload())
            ->assertSessionHasErrors('new_city.name');

        $this->assertNull($this->member());
    }

    public function test_a_typed_city_needs_its_state(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload([
            'new_city' => ['name' => 'Orchha'],
        ]))->assertSessionHasErrors('new_city.state');

        $this->assertSame(0, City::where('name', 'Orchha')->count());
    }

    public function test_two_businesses_of_the_same_name_cannot_share_a_city(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $jhansi = $this->city('Jhansi', 'Uttar Pradesh');

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload(['city_id' => $jhansi->id]));

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload([
            'email' => 'second@example.com',
            'city_id' => $jhansi->id,
        ]))->assertSessionHasErrors('business_name');

        $this->assertSame(0, User::where('email', 'second@example.com')->count());
    }

    public function test_the_same_city_name_in_another_state_gets_its_own_slug(): void
    {
        $directory = app(CityDirectoryService::class);

        $first = $directory->resolveOrCreate('Springfield', 'Kerala');
        $second = $directory->resolveOrCreate('Springfield', 'Bihar');

        // City slugs are globally unique because they open a microsite URL.
        $this->assertNotSame($first->id, $second->id);
        $this->assertSame('springfield', $first->slug);
        $this->assertSame('springfield-bihar', $second->slug);
    }

    public function test_the_commission_partner_form_no_longer_asks_for_cities(): void
    {
        $branch = $this->makeBranchManager(30);

        $response = $this->actingAs($branch)->get('/branch/commission-partners/create');

        $response->assertOk();
        $response->assertDontSee('data-city-picker', false);
        $response->assertDontSee('new_cities', false);
        $response->assertSee('Commission Partner Details', false);
    }

    public function test_a_commission_partner_can_be_created_without_any_city(): void
    {
        $branch = $this->makeBranchManager(30);

        $this->actingAs($branch)->post('/branch/commission-partners', [
            'name' => 'Cityless Partner',
            'email' => 'cityless@example.com',
            'password' => 'a-strong-password',
            'commission_percentage' => 20,
        ])->assertRedirect(route('branch.commission-partners.index'));

        $partner = User::where('email', 'cityless@example.com')->sole();

        $this->assertTrue($partner->hasRole('commission_partner'));
        $this->assertSame(0, $partner->cities()->count());
    }
}
