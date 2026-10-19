<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Rules\AadhaarNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Personal, address, nominee and KYC details on the Branch Manager and
 * Commission Partner forms.
 *
 * KYC is the part with consequences: Aadhaar and PAN are encrypted at rest and
 * only ever shown masked, and the photo / document scans live on a private
 * disk, reachable only by the people who manage that account.
 */
class MemberProfileKycTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Storage::fake('kyc');
        Storage::fake('public');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('super_admin');

        return $user;
    }

    /** A checksum-valid Aadhaar number, built rather than borrowed. */
    private function aadhaar(string $prefix = '23412341234'): string
    {
        return $prefix.AadhaarNumber::checkDigit($prefix);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function branchManagerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Asha Verma',
            'email' => 'asha.bm@example.com',
            'password' => 'a-strong-password',
            'mobile' => '9876543210',
            'commission_percentage' => 30,

            'salutation' => 'Ms.',
            'relation_type' => 'd/o',
            'relative_name' => 'Ram Kumar Verma',
            'gender' => 'female',
            'date_of_birth' => '1990-05-14',
            'blood_group' => 'B+',
            'alternate_mobile' => '+91 91234 56780',
            'address_line1' => '12 Station Road',
            'city' => 'Jhansi',
            'state' => 'Uttar Pradesh',
            'pincode' => '284001',
            'nominee_name' => 'Ravi Verma',
            'nominee_relation' => 'brother',
            'nominee_mobile' => '9988776655',
            'aadhaar_number' => '2341 2341 234'.substr($this->aadhaar(), -1),
            'pan_number' => 'bqzpk4321m',
        ], $overrides);
    }

    /* -------------------------------------------------------------- create */

    public function test_a_branch_manager_is_created_with_their_profile(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->branchManagerPayload())
            ->assertRedirect(route('admin.branch-managers.index'))
            ->assertSessionHasNoErrors();

        $manager = User::where('email', 'asha.bm@example.com')->sole();
        $profile = $manager->profile;

        $this->assertSame('Ram Kumar Verma', $profile->relative_name);
        $this->assertSame('D/O Ram Kumar Verma', $profile->relationLine());
        $this->assertSame('1990-05-14', $profile->date_of_birth->toDateString());
        $this->assertSame('9123456780', $profile->alternate_mobile, 'mobiles are normalised to 10 digits');
        $this->assertSame('284001', $profile->pincode);
        $this->assertSame('brother', $profile->nominee_relation);
        $this->assertSame($this->aadhaar(), $profile->aadhaar_number, 'spaces are stripped');
        $this->assertSame('BQZPK4321M', $profile->pan_number, 'PAN is upper-cased');
    }

    public function test_every_profile_field_is_optional(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', [
                'name' => 'Bare Minimum',
                'email' => 'bare@example.com',
                'password' => 'a-strong-password',
                'commission_percentage' => 30,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertNotNull(User::where('email', 'bare@example.com')->sole());
    }

    public function test_a_commission_partner_is_created_with_a_mobile_and_profile(): void
    {
        $branch = $this->makeBranchManager(30);

        $this->actingAs($branch)
            ->post('/branch/commission-partners', [
                'name' => 'New Partner',
                'email' => 'new.partner@example.com',
                'password' => 'a-strong-password',
                'mobile' => '09876543219',
                'commission_percentage' => 20,
                'city' => 'Jhansi',
                'nominee_name' => 'Sunita',
            ])
            ->assertRedirect(route('branch.commission-partners.index'))
            ->assertSessionHasNoErrors();

        $partner = User::where('email', 'new.partner@example.com')->sole();

        $this->assertSame('9876543219', $partner->mobile);
        $this->assertSame('Jhansi', $partner->profile->city);
        $this->assertSame('Sunita', $partner->profile->nominee_name);
    }

    public function test_a_sub_admin_is_created_and_updated_with_their_profile(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post('/admin/sub-admins', [
                'name' => 'Meera Sub',
                'email' => 'meera.sub@example.com',
                'password' => 'a-strong-password',
                'mobile' => '9811122233',
                'gender' => 'female',
                'city' => 'Agra',
                'aadhaar_number' => $this->aadhaar(),
                'photo' => UploadedFile::fake()->image('me.jpg', 600, 800),
            ])
            ->assertSessionHasNoErrors();

        $subAdmin = User::where('email', 'meera.sub@example.com')->sole();
        $this->assertSame('Agra', $subAdmin->profile->city);
        $this->assertSame($this->aadhaar(), $subAdmin->profile->aadhaar_number);
        Storage::disk('kyc')->assertExists($subAdmin->profile->photo_path);

        $this->actingAs($admin)
            ->get("/admin/sub-admins/{$subAdmin->id}/edit")
            ->assertOk()
            ->assertSee('XXXX XXXX '.substr($this->aadhaar(), -4), false)
            ->assertDontSee($this->aadhaar(), false);

        $this->actingAs($admin)
            ->put("/admin/sub-admins/{$subAdmin->id}", [
                'name' => 'Meera Sub',
                'email' => 'meera.sub@example.com',
                'mobile' => '9811122233',
                'city' => 'Mathura',
            ])
            ->assertSessionHasNoErrors();

        $profile = $subAdmin->profile()->first();
        $this->assertSame('Mathura', $profile->city);
        $this->assertSame($this->aadhaar(), $profile->aadhaar_number, 'blank Aadhaar keeps the stored one');
    }

    public function test_a_bad_sub_admin_profile_creates_nothing(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/sub-admins', [
                'name' => 'Bad', 'email' => 'bad.sub@example.com', 'password' => 'a-strong-password',
                'pincode' => '12345',
            ])
            ->assertSessionHasErrors('pincode');

        $this->assertSame(0, User::where('email', 'bad.sub@example.com')->count());
    }

    public function test_only_super_admin_can_open_a_sub_admins_kyc_besides_themselves(): void
    {
        $subAdmin = $this->memberWithPhoto($this->makeSubAdmin(['branch-managers']));

        $this->actingAs($this->superAdmin())->get("/kyc/{$subAdmin->id}/photo")->assertOk();
        $this->actingAs($subAdmin)->get("/kyc/{$subAdmin->id}/photo")->assertOk();
        $this->actingAs($this->makeSubAdmin(['branch-managers']))->get("/kyc/{$subAdmin->id}/photo")->assertNotFound();
        $this->actingAs($this->makeBranchManager(30, $subAdmin))->get("/kyc/{$subAdmin->id}/photo")->assertNotFound();
    }

    /* ----------------------------------------------------- KYC at rest */

    public function test_aadhaar_and_pan_are_encrypted_in_the_database(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/branch-managers', $this->branchManagerPayload());

        $raw = DB::table('user_profiles')->first();

        $this->assertStringNotContainsString($this->aadhaar(), (string) $raw->aadhaar_number);
        $this->assertStringNotContainsString('BQZPK4321M', (string) $raw->pan_number);
        $this->assertNotEmpty($raw->aadhaar_number);
    }

    public function test_the_edit_form_shows_kyc_numbers_masked_never_in_full(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->post('/admin/branch-managers', $this->branchManagerPayload());
        $manager = User::where('email', 'asha.bm@example.com')->sole();

        $page = $this->actingAs($admin)->get("/admin/branch-managers/{$manager->id}/edit");

        $page->assertOk();
        $page->assertSee('XXXX XXXX '.substr($this->aadhaar(), -4), false);
        $page->assertSee('XXXXX4321X', false);
        $page->assertDontSee($this->aadhaar(), false);
        $page->assertDontSee(chunk_split($this->aadhaar(), 4, ' '), false);
        $page->assertDontSee('BQZPK4321M', false);
    }

    public function test_a_blank_kyc_number_on_edit_keeps_the_one_on_record(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->post('/admin/branch-managers', $this->branchManagerPayload());
        $manager = User::where('email', 'asha.bm@example.com')->sole();

        $this->actingAs($admin)->put("/admin/branch-managers/{$manager->id}", $this->branchManagerPayload([
            'aadhaar_number' => '',
            'pan_number' => '',
            'city' => 'Lucknow',
        ]))->assertSessionHasNoErrors();

        $profile = $manager->profile()->first();
        $this->assertSame($this->aadhaar(), $profile->aadhaar_number);
        $this->assertSame('BQZPK4321M', $profile->pan_number);
        $this->assertSame('Lucknow', $profile->city);
    }

    public function test_a_kyc_number_can_be_removed_deliberately(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->post('/admin/branch-managers', $this->branchManagerPayload());
        $manager = User::where('email', 'asha.bm@example.com')->sole();

        $this->actingAs($admin)->put("/admin/branch-managers/{$manager->id}", $this->branchManagerPayload([
            'aadhaar_number' => '',
            'clear_aadhaar_number' => '1',
        ]));

        $this->assertNull($manager->profile()->first()->aadhaar_number);
    }

    /* ---------------------------------------------------------- validation */

    public function test_a_mistyped_aadhaar_is_caught_by_its_check_digit(): void
    {
        $wrongLast = ((int) substr($this->aadhaar(), -1) + 1) % 10;

        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->branchManagerPayload([
                'aadhaar_number' => '23412341234'.$wrongLast,
            ]))
            ->assertSessionHasErrors('aadhaar_number');
    }

    public function test_kyc_and_address_formats_are_enforced(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->branchManagerPayload([
                'pan_number' => 'ABCD12345F',
                'pincode' => '028400',
                'date_of_birth' => now()->subYears(17)->toDateString(),
                'alternate_mobile' => '9876543210',   // same as the main mobile
            ]))
            ->assertSessionHasErrors(['pan_number', 'pincode', 'date_of_birth', 'alternate_mobile']);

        $this->assertSame(0, User::where('email', 'asha.bm@example.com')->count(), 'nothing half-created');
    }

    public function test_a_commission_partner_mobile_must_be_unique(): void
    {
        $branch = $this->makeBranchManager(30);
        $existing = $this->makeCommissionPartner($branch, 20);
        $existing->update(['mobile' => '9876500000']);

        $this->actingAs($branch)
            ->post('/branch/commission-partners', [
                'name' => 'Dup', 'email' => 'dup@example.com', 'password' => 'a-strong-password',
                'mobile' => '9876500000', 'commission_percentage' => 20,
            ])
            ->assertSessionHasErrors('mobile');
    }

    /* ---------------------------------------------------------------- files */

    public function test_the_photo_is_cropped_to_35_by_45_and_stored_privately(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/branch-managers', $this->branchManagerPayload([
            'photo' => UploadedFile::fake()->image('me.jpg', 1200, 900),
        ]))->assertSessionHasNoErrors();

        $path = User::where('email', 'asha.bm@example.com')->sole()->profile->photo_path;

        Storage::disk('kyc')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
        $this->assertStringEndsWith('.webp', $path);

        [$w, $h] = getimagesizefromstring(Storage::disk('kyc')->get($path));
        $this->assertEqualsWithDelta(35 / 45, $w / $h, 0.01);
        $this->assertLessThanOrEqual(700, $w);
    }

    public function test_a_large_document_scan_is_shrunk(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/branch-managers', $this->branchManagerPayload([
            'aadhaar_file' => UploadedFile::fake()->image('aadhaar.png', 3200, 2000),
        ]))->assertSessionHasNoErrors();

        $path = User::where('email', 'asha.bm@example.com')->sole()->profile->aadhaar_file_path;
        [$w, $h] = getimagesizefromstring(Storage::disk('kyc')->get($path));

        $this->assertSame(2000, max($w, $h));
    }

    public function test_replacing_or_removing_a_file_deletes_the_old_one(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->post('/admin/branch-managers', $this->branchManagerPayload([
            'pan_file' => UploadedFile::fake()->image('pan.jpg', 800, 500),
        ]));
        $manager = User::where('email', 'asha.bm@example.com')->sole();
        $first = $manager->profile->pan_file_path;

        $this->actingAs($admin)->put("/admin/branch-managers/{$manager->id}", $this->branchManagerPayload([
            'pan_file' => UploadedFile::fake()->image('pan2.jpg', 800, 500),
        ]));
        $second = $manager->profile()->first()->pan_file_path;

        $this->assertNotSame($first, $second);
        Storage::disk('kyc')->assertMissing($first);
        Storage::disk('kyc')->assertExists($second);

        $this->actingAs($admin)->put("/admin/branch-managers/{$manager->id}", $this->branchManagerPayload([
            'remove_files' => ['pan_file'],
        ]));

        $this->assertNull($manager->profile()->first()->pan_file_path);
        Storage::disk('kyc')->assertMissing($second);
    }

    public function test_a_non_image_upload_is_refused(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/branch-managers', $this->branchManagerPayload([
                'aadhaar_file' => UploadedFile::fake()->create('aadhaar.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('aadhaar_file');
    }

    /* --------------------------------------------------------- who can see */

    private function memberWithPhoto(User $member): User
    {
        Storage::disk('kyc')->put("members/{$member->id}/photo-x.webp", 'image-bytes');
        UserProfile::create(['user_id' => $member->id, 'photo_path' => "members/{$member->id}/photo-x.webp"]);

        return $member;
    }

    public function test_super_admin_can_open_anyones_kyc_file(): void
    {
        $manager = $this->memberWithPhoto($this->makeBranchManager(30));

        $this->actingAs($this->superAdmin())
            ->get("/kyc/{$manager->id}/photo")
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_kyc_files_are_never_cached(): void
    {
        $manager = $this->memberWithPhoto($this->makeBranchManager(30));

        $response = $this->actingAs($this->superAdmin())->get("/kyc/{$manager->id}/photo");

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_a_branch_manager_sees_their_own_partners_kyc_but_no_one_elses(): void
    {
        $branch = $this->makeBranchManager(30);
        $mine = $this->memberWithPhoto($this->makeCommissionPartner($branch, 20));
        $theirs = $this->memberWithPhoto($this->makeCommissionPartner($this->makeBranchManager(30), 20));

        $this->actingAs($branch)->get("/kyc/{$mine->id}/photo")->assertOk();
        $this->actingAs($branch)->get("/kyc/{$theirs->id}/photo")->assertNotFound();
    }

    public function test_a_sub_admin_sees_only_their_own_branch_managers_kyc(): void
    {
        $subAdmin = $this->makeSubAdmin(['branch-managers']);
        $mine = $this->memberWithPhoto($this->makeBranchManager(30, $subAdmin));
        $theirs = $this->memberWithPhoto($this->makeBranchManager(30, $this->makeSubAdmin(['branch-managers'])));

        $this->actingAs($subAdmin)->get("/kyc/{$mine->id}/photo")->assertOk();
        $this->actingAs($subAdmin)->get("/kyc/{$theirs->id}/photo")->assertNotFound();
    }

    public function test_a_partner_cannot_open_a_fellow_partners_kyc(): void
    {
        $branch = $this->makeBranchManager(30);
        $a = $this->makeCommissionPartner($branch, 20);
        $b = $this->memberWithPhoto($this->makeCommissionPartner($branch, 20));

        $this->actingAs($a)->get("/kyc/{$b->id}/photo")->assertNotFound();
    }

    public function test_guests_cannot_open_kyc_files(): void
    {
        $manager = $this->memberWithPhoto($this->makeBranchManager(30));

        $this->get("/kyc/{$manager->id}/photo")->assertRedirect(route('login'));
    }

    public function test_kyc_files_are_not_reachable_through_the_public_storage_route(): void
    {
        $manager = $this->memberWithPhoto($this->makeBranchManager(30));
        $path = $manager->profile->photo_path;

        $this->get('/storage/'.$path)->assertNotFound();
    }
}
