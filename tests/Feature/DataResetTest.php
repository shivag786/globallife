<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Lead;
use App\Models\User;
use App\Services\DataResetService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Super Admin → Erase All Data: locked behind DATA_RESET_PASSWORD and a typed
 * phrase, backs up first, wipes business data only, keeps the site running.
 */
class DataResetTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private const PASSWORD = 'reset-pass-2026';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Storage::fake('public');
        Storage::fake('kyc');
        Storage::fake('local');
        config(['data_reset.password' => self::PASSWORD]);
    }

    private function superAdmin(): User
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('super_admin');

        return $admin;
    }

    public function test_every_table_is_classified(): void
    {
        $classified = array_merge(DataResetService::WIPE, DataResetService::PARTIAL, DataResetService::KEEP);
        $unclassified = array_diff(app(DataResetService::class)->allTables(), $classified);

        $this->assertSame([], array_values($unclassified),
            'New table(s) must be added to DataResetService WIPE, PARTIAL or KEEP: '.implode(', ', $unclassified));
    }

    public function test_only_super_admin_can_open_it(): void
    {
        $this->actingAs($this->makeSubAdmin(['blog', 'employees']))->get('/admin/system/reset')->assertForbidden();

        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');
        $this->actingAs($admin)->get('/admin/system/reset')->assertForbidden();

        $this->actingAs($this->superAdmin())->get('/admin/system/reset')->assertOk()->assertSee('Step 1');
    }

    public function test_it_is_switched_off_without_a_reset_password(): void
    {
        config(['data_reset.password' => null]);
        $super = $this->superAdmin();

        $this->actingAs($super)->get('/admin/system/reset')->assertOk()->assertSee('switched off');
        $this->actingAs($super)->post('/admin/system/reset/unlock', ['reset_password' => ''])->assertSessionHasErrors('reset_password');
        $this->actingAs($super)->delete('/admin/system/reset', ['confirmation' => 'ERASE ALL DATA', 'understood' => '1'])->assertSessionHasErrors('reset_password');
    }

    public function test_a_wrong_password_or_phrase_erases_nothing(): void
    {
        Lead::create(['name' => 'Keep Me', 'email' => 'k@example.com', 'source' => 'chatbot']);
        $super = $this->superAdmin();

        $this->actingAs($super)->post('/admin/system/reset/unlock', ['reset_password' => 'wrong-password'])
            ->assertSessionHasErrors('reset_password');
        $this->actingAs($super)->delete('/admin/system/reset', ['confirmation' => 'ERASE ALL DATA', 'understood' => '1'])
            ->assertSessionHasErrors('reset_password');

        $this->actingAs($super)->post('/admin/system/reset/unlock', ['reset_password' => self::PASSWORD])->assertSessionHasNoErrors();
        $this->actingAs($super)->delete('/admin/system/reset', ['confirmation' => 'erase all data', 'understood' => '1'])
            ->assertSessionHasErrors('confirmation');

        // Right phrase, but the "I understand" box was not ticked.
        $this->actingAs($super)->delete('/admin/system/reset', ['confirmation' => 'ERASE ALL DATA'])
            ->assertSessionHasErrors('understood');

        $this->assertSame(1, Lead::count());
        $this->assertSame([], app(DataResetService::class)->backups());
    }

    public function test_erase_backs_up_wipes_business_data_and_keeps_the_site(): void
    {
        $super = $this->superAdmin();
        $otherSuper = $this->superAdmin();

        // Business data that must go.
        $bm = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($bm, 10);
        $customer = User::factory()->create();
        $customer->assignRole('customer');
        Lead::create(['name' => 'Old Lead', 'email' => 'l@example.com', 'source' => 'chatbot']);
        DB::table('addresses')->insert(['user_id' => $customer->id, 'name' => 'C', 'phone' => '9999999999',
            'address' => 'x', 'city' => 'y', 'state' => 'z', 'pincode' => '123456', 'created_at' => now(), 'updated_at' => now()]);

        // Site content that must stay, including a post written by a user who is removed.
        $post = BlogPost::create(['title' => 'Kept post', 'slug' => 'kept-post', 'content' => 'x', 'status' => 'published', 'author_id' => $bm->id]);
        app(SettingsService::class)->set('site_logo', 'uploads/logo-kept.png');

        Storage::disk('public')->put('uploads/logo-kept.png', 'logo');
        Storage::disk('public')->put('uploads/vip-orphan.png', 'vip photo');
        Storage::disk('kyc')->put('hr/1/documents/aadhaar.pdf', 'kyc');

        $this->actingAs($super)->post('/admin/system/reset/unlock', ['reset_password' => self::PASSWORD]);
        $this->actingAs($super)->delete('/admin/system/reset', ['confirmation' => 'ERASE ALL DATA', 'understood' => '1'])
            ->assertRedirect(route('admin.system.reset'))
            ->assertSessionHas('status');

        // Only the Super Admins are left, with their roles.
        $this->assertEqualsCanonicalizing([$super->id, $otherSuper->id], User::pluck('id')->all());
        $this->assertTrue($super->fresh()->hasRole('super_admin'));
        $this->assertSame(0, Lead::count());
        $this->assertSame(0, DB::table('addresses')->count());
        $this->assertDatabaseMissing('users', ['id' => $partner->id]);

        // Content and settings stay; the orphaned post gets a living author.
        $this->assertSame($super->id, $post->fresh()->author_id);
        $this->assertSame('uploads/logo-kept.png', app(SettingsService::class)->get('site_logo'));
        $this->assertGreaterThan(0, DB::table('roles')->count());
        $this->assertGreaterThan(0, DB::table('leave_types')->count());

        // Files: referenced ones stay, the rest move into the backup.
        $backup = app(DataResetService::class)->backups()[0]['name'];
        Storage::disk('public')->assertExists('uploads/logo-kept.png');
        Storage::disk('public')->assertMissing('uploads/vip-orphan.png');
        Storage::disk('kyc')->assertMissing('hr/1/documents/aadhaar.pdf');
        Storage::disk('local')->assertExists("backups/{$backup}/files/public/uploads/vip-orphan.png");
        Storage::disk('local')->assertExists("backups/{$backup}/files/kyc/hr/1/documents/aadhaar.pdf");

        // The backup has the erased rows, and can be downloaded.
        $sql = Storage::disk('local')->get("backups/{$backup}/database.sql");
        $this->assertStringContainsString('INSERT INTO `leads`', $sql);
        $this->assertStringContainsString('Old Lead', $sql);
        $this->actingAs($super)->get("/admin/system/backups/{$backup}")->assertOk();

        // The Super Admin is still signed in and the panel works.
        $this->actingAs($super)->get('/admin/dashboard')->assertOk();
        $this->get('/')->assertOk();

        // The unlock was used up.
        $this->actingAs($super)->delete('/admin/system/reset', ['confirmation' => 'ERASE ALL DATA', 'understood' => '1'])
            ->assertSessionHasErrors('reset_password');
    }
}
