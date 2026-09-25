<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\MobileNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Mobile numbers are stored as exactly 10 digits and are unique per account.
 *
 * Input arrives typed, pasted or autofilled, so a country code or trunk prefix
 * has to be trimmed server-side — the browser tidy-up is a convenience, never
 * the thing that makes the data correct.
 */
class MobileNumberTest extends TestCase
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Manager',
            'email' => 'new.manager@example.com',
            'password' => 'a-strong-password',
            'commission_percentage' => 30,
        ], $overrides);
    }

    /**
     * @return array<int, array{0: ?string, 1: ?string}>
     */
    public static function numbers(): array
    {
        return [
            'plain ten digits' => ['9876543210', '9876543210'],
            'country code with spaces' => ['+91 98765 43210', '9876543210'],
            'country code with dashes' => ['+91-98765-43210', '9876543210'],
            'no plus, country code' => ['919876543210', '9876543210'],
            'leading trunk zero' => ['09876543210', '9876543210'],
            'brackets and padding' => ['  +91 (98765) 43210  ', '9876543210'],
            'empty becomes null' => ['', null],
            'null stays null' => [null, null],
        ];
    }

    #[DataProvider('numbers')]
    public function test_any_shape_reduces_to_the_last_ten_digits(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, MobileNumber::normalise($input));
    }

    public function test_a_pasted_number_with_a_country_code_is_stored_as_ten_digits(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->payload(['mobile' => '+91 98765 43210']))
            ->assertRedirect(route('admin.branch-managers.index'));

        $this->assertSame(
            '9876543210',
            User::where('email', 'new.manager@example.com')->value('mobile'),
        );
    }

    public function test_a_duplicate_mobile_is_refused(): void
    {
        $existing = $this->makeBranchManager(30);
        $existing->forceFill(['mobile' => '9876543210'])->save();

        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->payload(['mobile' => '9876543210']))
            ->assertSessionHasErrors('mobile');

        $this->assertSame(0, User::where('email', 'new.manager@example.com')->count());
    }

    public function test_the_same_number_in_another_format_is_still_a_duplicate(): void
    {
        $existing = $this->makeBranchManager(30);
        $existing->forceFill(['mobile' => '9876543210'])->save();

        // Normalisation runs first, so this is recognised as the same number.
        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->payload(['mobile' => '+91 98765 43210']))
            ->assertSessionHasErrors('mobile');

        $this->assertSame(0, User::where('email', 'new.manager@example.com')->count());
    }

    public function test_too_few_digits_is_refused_rather_than_padded(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->payload(['mobile' => '98765']))
            ->assertSessionHasErrors('mobile');

        $this->assertSame(0, User::where('email', 'new.manager@example.com')->count());
    }

    public function test_mobile_stays_optional(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->payload())
            ->assertRedirect(route('admin.branch-managers.index'));

        $this->assertNull(User::where('email', 'new.manager@example.com')->value('mobile'));
    }

    public function test_editing_a_manager_keeps_their_own_number(): void
    {
        $manager = $this->makeBranchManager(30);
        $manager->forceFill(['mobile' => '9876543210'])->save();

        // Their own number must not collide with itself.
        $this->actingAs($this->superAdmin())
            ->put("/admin/branch-managers/{$manager->id}", [
                'name' => 'Renamed',
                'email' => $manager->email,
                'mobile' => '+91 98765 43210',
                'commission_percentage' => 30,
            ])
            ->assertRedirect(route('admin.branch-managers.index'))
            ->assertSessionHasNoErrors();

        $manager->refresh();
        $this->assertSame('Renamed', $manager->name);
        $this->assertSame('9876543210', $manager->mobile);
    }

    public function test_a_manager_cannot_take_another_accounts_number(): void
    {
        $other = $this->makeBranchManager(30);
        $other->forceFill(['mobile' => '9000000001'])->save();

        $manager = $this->makeBranchManager(30);

        $this->actingAs($this->superAdmin())
            ->put("/admin/branch-managers/{$manager->id}", [
                'name' => $manager->name,
                'email' => $manager->email,
                'mobile' => '9000000001',
                'commission_percentage' => 30,
            ])
            ->assertSessionHasErrors('mobile');

        $this->assertNull($manager->refresh()->mobile);
    }

    public function test_a_vip_member_cannot_take_a_branch_managers_number(): void
    {
        // users.mobile is written from the VIP profile too, so the uniqueness
        // rule has to hold across both forms or it means nothing.
        $manager = $this->makeBranchManager(30);
        $manager->forceFill(['mobile' => '9000000002'])->save();

        $partner = $this->makeCommissionPartner($manager, 25);
        [$member] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        $this->actingAs($member)
            ->put('/vip/profile', ['business_name' => 'Biz', 'mobile' => '+91 90000 00002'])
            ->assertSessionHasErrors('mobile');

        $this->assertNull($member->refresh()->mobile);
    }

    public function test_the_form_marks_the_field_up_for_the_browser_tidy_up(): void
    {
        $response = $this->actingAs($this->superAdmin())->get('/admin/branch-managers/create');

        $response->assertOk();
        $response->assertSee('data-mobile-input', false);
        $response->assertSee('maxlength="10"', false);
        $response->assertSee('inputmode="numeric"', false);
    }
}
