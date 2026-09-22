<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\User;
use App\Services\CityDirectoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The Branch Manager's state → city picker when adding a Commission Partner.
 *
 * A city may be chosen from the state's list or typed in when it is missing.
 * Either way it ends up attached to the Branch Manager's branch, because a
 * partner may only serve cities inside it — so assigning is also how a Branch
 * Manager takes on new territory.
 */
class BranchCityPickerTest extends TestCase
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
            'name' => 'New Partner',
            'email' => 'new.partner@example.com',
            'password' => 'a-strong-password',
            'commission_percentage' => 20,
        ], $overrides);
    }

    public function test_the_form_offers_states_with_their_cities_for_the_cascade(): void
    {
        $branch = $this->makeBranchManager(30);
        $jhansi = $this->city('Jhansi', 'Uttar Pradesh');
        $indore = $this->city('Indore', 'Madhya Pradesh');

        $response = $this->actingAs($branch)->get('/branch/commission-partners/create');

        $response->assertOk();
        $response->assertSee('data-city-picker', false);
        // States in the dropdown...
        $response->assertSee('Uttar Pradesh', false);
        $response->assertSee('Madhya Pradesh', false);
        // ...and the state -> city map the picker filters on.
        $response->assertSee('Jhansi', false);
        $response->assertSee('Indore', false);
        $response->assertSee((string) $jhansi->id, false);
        $response->assertSee((string) $indore->id, false);
        // Plus the manual escape hatch.
        $response->assertSee('__other__', false);
    }

    public function test_the_states_and_cities_are_grouped_by_state(): void
    {
        $this->city('Jhansi', 'Uttar Pradesh');
        $this->city('Kanpur', 'Uttar Pradesh');
        $this->city('Indore', 'Madhya Pradesh');
        // Inactive cities are not offered.
        $this->city('Ghost Town', 'Kerala')->update(['status' => 'inactive']);

        $directory = app(CityDirectoryService::class);

        $this->assertSame(['Madhya Pradesh', 'Uttar Pradesh'], $directory->states());

        $map = $directory->citiesByState();
        $this->assertSame(['Jhansi', 'Kanpur'], array_column($map['Uttar Pradesh'], 'name'));
        $this->assertSame(['Indore'], array_column($map['Madhya Pradesh'], 'name'));
        $this->assertArrayNotHasKey('Kerala', $map);
    }

    public function test_a_partner_can_be_created_from_cities_chosen_in_the_dropdown(): void
    {
        $branch = $this->makeBranchManager(30);
        $jhansi = $this->city('Jhansi', 'Uttar Pradesh');
        $kanpur = $this->city('Kanpur', 'Uttar Pradesh');

        $this->actingAs($branch)->post('/branch/commission-partners', $this->payload([
            'cities' => [$jhansi->id, $kanpur->id],
        ]))->assertRedirect(route('branch.commission-partners.index'));

        $partner = User::where('email', 'new.partner@example.com')->sole();

        $this->assertSame(
            ['Jhansi', 'Kanpur'],
            $partner->cities()->orderBy('name')->pluck('name')->all(),
        );
        // Chosen cities join the branch, so the assignment stays valid.
        $this->assertEqualsCanonicalizing(
            [$jhansi->id, $kanpur->id],
            $branch->branchCities()->pluck('cities.id')->all(),
        );
    }

    public function test_a_city_typed_in_is_created_and_attached_to_the_branch(): void
    {
        $branch = $this->makeBranchManager(30);

        $this->actingAs($branch)->post('/branch/commission-partners', $this->payload([
            'new_cities' => [['name' => 'Orchha', 'state' => 'Madhya Pradesh']],
        ]))->assertRedirect();

        $orchha = City::where('name', 'Orchha')->sole();
        $this->assertSame('Madhya Pradesh', $orchha->state);
        $this->assertSame('orchha', $orchha->slug);
        $this->assertSame('active', $orchha->status);

        $partner = User::where('email', 'new.partner@example.com')->sole();
        $this->assertSame([$orchha->id], $partner->cities()->pluck('cities.id')->all());
        $this->assertContains($orchha->id, $branch->branchCities()->pluck('cities.id')->all());
    }

    public function test_chosen_and_typed_cities_can_be_mixed_in_one_submit(): void
    {
        $branch = $this->makeBranchManager(30);
        $jhansi = $this->city('Jhansi', 'Uttar Pradesh');

        $this->actingAs($branch)->post('/branch/commission-partners', $this->payload([
            'cities' => [$jhansi->id],
            'new_cities' => [
                ['name' => 'Orchha', 'state' => 'Madhya Pradesh'],
                ['name' => 'Datia', 'state' => 'Madhya Pradesh'],
            ],
        ]))->assertRedirect();

        $partner = User::where('email', 'new.partner@example.com')->sole();

        $this->assertSame(
            ['Datia', 'Jhansi', 'Orchha'],
            $partner->cities()->orderBy('name')->pluck('name')->all(),
        );
    }

    public function test_typing_a_city_that_already_exists_reuses_it_instead_of_duplicating(): void
    {
        $branch = $this->makeBranchManager(30);
        $existing = $this->city('Jhansi', 'Uttar Pradesh');

        $this->actingAs($branch)->post('/branch/commission-partners', $this->payload([
            // Different casing and padding — still the same city.
            'new_cities' => [['name' => '  jhansi ', 'state' => 'uttar pradesh']],
        ]))->assertRedirect();

        $this->assertSame(1, City::where('state', 'Uttar Pradesh')->count());

        $partner = User::where('email', 'new.partner@example.com')->sole();
        $this->assertSame([$existing->id], $partner->cities()->pluck('cities.id')->all());
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

    public function test_a_partner_must_be_given_at_least_one_city(): void
    {
        $branch = $this->makeBranchManager(30);

        $this->actingAs($branch)->post('/branch/commission-partners', $this->payload())
            ->assertSessionHasErrors('cities');

        // A blank typed row does not count as a city either.
        $this->actingAs($branch)->post('/branch/commission-partners', $this->payload([
            'new_cities' => [['name' => '', 'state' => '']],
        ]))->assertSessionHasErrors();

        $this->assertSame(0, User::where('email', 'new.partner@example.com')->count());
    }

    public function test_a_typed_city_needs_both_a_name_and_a_state(): void
    {
        $branch = $this->makeBranchManager(30);

        $this->actingAs($branch)->post('/branch/commission-partners', $this->payload([
            'new_cities' => [['name' => 'Orchha']],
        ]))->assertSessionHasErrors('new_cities.0.state');

        $this->assertSame(0, City::where('name', 'Orchha')->count());
    }

    public function test_editing_keeps_the_partners_cities_and_can_add_a_typed_one(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        $jhansi = $this->city('Jhansi', 'Uttar Pradesh');
        $partner->cities()->sync([$jhansi->id]);

        // The edit form shows the existing city as a chip.
        $this->actingAs($branch)->get("/branch/commission-partners/{$partner->id}/edit")
            ->assertOk()
            ->assertSee('Jhansi, Uttar Pradesh', false)
            ->assertSee('name="cities[]"', false);

        $this->actingAs($branch)->put("/branch/commission-partners/{$partner->id}", [
            'name' => $partner->name,
            'email' => $partner->email,
            'commission_percentage' => 25,
            'cities' => [$jhansi->id],
            'new_cities' => [['name' => 'Orchha', 'state' => 'Madhya Pradesh']],
        ])->assertRedirect(route('branch.commission-partners.index'));

        $this->assertSame(
            ['Jhansi', 'Orchha'],
            $partner->fresh()->cities()->orderBy('name')->pluck('name')->all(),
        );
    }

    public function test_only_a_branch_manager_can_use_the_form(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($partner)->get('/branch/commission-partners/create')->assertForbidden();
        $this->actingAs($partner)->post('/branch/commission-partners', $this->payload([
            'new_cities' => [['name' => 'Orchha', 'state' => 'Madhya Pradesh']],
        ]))->assertForbidden();

        $this->assertSame(0, City::where('name', 'Orchha')->count());
    }
}
