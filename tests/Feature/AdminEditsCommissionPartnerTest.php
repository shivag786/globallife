<?php

namespace Tests\Feature;

use App\Models\CommissionPartnerUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Super Admin can edit any Commission Partner, and every such edit leaves a
 * note for that partner's Branch Manager: what changed, who did it, why, and
 * whether the Branch Manager has seen it.
 */
class AdminEditsCommissionPartnerTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active', 'name' => 'Head Office']);
        $user->assignRole('super_admin');

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(User $partner, array $overrides = []): array
    {
        return array_merge([
            'name' => $partner->name,
            'email' => $partner->email,
            'mobile' => $partner->mobile,
            'commission_percentage' => $partner->commission_percentage,
        ], $overrides);
    }

    /* ---------------------------------------------------------------- admin */

    public function test_super_admin_can_open_any_partners_edit_screen(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 20);

        $this->actingAs($this->superAdmin())
            ->get("/admin/commission-partners/{$partner->id}/edit")
            ->assertOk()
            ->assertSee('name="commission_percentage"', false)
            ->assertSee('Reason for the change', false);

        $this->actingAs($this->superAdmin())->get('/admin/commission-partners')
            ->assertOk()
            ->assertSee(route('admin.commission-partners.edit', $partner), false);
    }

    public function test_super_admin_changes_commission_and_details_and_a_note_is_recorded(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 20);
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, [
                'commission_percentage' => 25,
                'name' => 'Renamed Partner',
                'mobile' => '+91 98765 43210',
                'note' => 'Rate revised after the September review',
            ]))
            ->assertRedirect(route('admin.commission-partners.edit', $partner))
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'notified'));

        $partner->refresh();
        $this->assertEquals(25, (float) $partner->commission_percentage);
        $this->assertSame('Renamed Partner', $partner->name);
        $this->assertSame('9876543210', $partner->mobile, 'the mobile is normalised as everywhere else');

        $note = CommissionPartnerUpdate::sole();
        $this->assertSame($partner->id, $note->commission_partner_id);
        $this->assertSame($branch->id, $note->branch_manager_id, 'addressed to the partner\'s Branch Manager');
        $this->assertSame($admin->id, $note->updated_by);
        $this->assertSame('Rate revised after the September review', $note->note);
        $this->assertNull($note->read_at);
        $this->assertSame(['name', 'mobile', 'commission_percentage'], array_keys($note->changed_fields));
        $this->assertEquals(20, (float) $note->changed_fields['commission_percentage'][0]);
        $this->assertEquals(25, (float) $note->changed_fields['commission_percentage'][1]);
    }

    public function test_only_fields_that_actually_changed_are_recorded(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 20);

        $this->actingAs($this->superAdmin())
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, ['commission_percentage' => '22.00']));

        $this->assertSame(['commission_percentage'], array_keys(CommissionPartnerUpdate::sole()->changed_fields));
    }

    public function test_saving_without_changes_or_a_reason_leaves_no_note(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 20);

        $this->actingAs($this->superAdmin())
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner))
            ->assertSessionHas('status', 'Nothing changed.');

        $this->assertSame(0, CommissionPartnerUpdate::count());
    }

    public function test_commission_cannot_exceed_the_branch_managers_own_share(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 20);

        $this->actingAs($this->superAdmin())
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, ['commission_percentage' => 31]))
            ->assertSessionHasErrors('commission_percentage');

        $this->assertEquals(20, (float) $partner->refresh()->commission_percentage);
        $this->assertSame(0, CommissionPartnerUpdate::count());
    }

    public function test_mobile_and_email_must_stay_unique(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 20);
        $other = $this->makeCommissionPartner($branch, 20);
        $other->update(['mobile' => '9123456780']);

        $this->actingAs($this->superAdmin())
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, [
                'email' => $other->email,
                'mobile' => '9123456780',
            ]))
            ->assertSessionHasErrors(['email', 'mobile']);
    }

    public function test_only_super_admin_can_edit_from_the_admin_panel(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 20);

        $subAdmin = $this->makeSubAdmin(['branch-managers']);

        $this->actingAs($subAdmin)->get("/admin/commission-partners/{$partner->id}/edit")->assertForbidden();
        $this->actingAs($subAdmin)
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, ['commission_percentage' => 25]))
            ->assertForbidden();

        $this->assertEquals(20, (float) $partner->refresh()->commission_percentage);
    }

    public function test_the_endpoint_only_works_on_commission_partners(): void
    {
        $branch = $this->makeBranchManager(30);

        $this->actingAs($this->superAdmin())
            ->get("/admin/commission-partners/{$branch->id}/edit")
            ->assertNotFound();
    }

    /* ---------------------------------------------------- branch manager */

    public function test_the_branch_manager_sees_the_note_on_their_dashboard(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 20);

        $this->actingAs($this->superAdmin())
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, [
                'commission_percentage' => 25,
                'note' => 'Rate revised after the September review',
            ]));

        $this->actingAs($branch)->get('/branch/dashboard')
            ->assertOk()
            ->assertSee('Updates from Super Admin', false)
            ->assertSee($partner->name, false)
            ->assertSee('Commission', false)
            ->assertSee('20%', false)
            ->assertSee('25%', false)
            ->assertSee('Rate revised after the September review', false)
            ->assertSee('Head Office', false);
    }

    public function test_the_partner_list_flags_the_changed_partner_and_its_page_shows_the_history(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 20);
        $untouched = $this->makeCommissionPartner($branch, 20);

        $this->actingAs($this->superAdmin())
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, ['commission_percentage' => 25]));

        $list = $this->actingAs($branch)->get('/branch/commission-partners');
        $list->assertOk();
        $list->assertSee('Updated by Super Admin', false);
        $this->assertSame(1, substr_count($list->getContent(), 'Updated by Super Admin'), 'only the changed partner is flagged');

        $this->actingAs($branch)->get("/branch/commission-partners/{$partner->id}/edit")
            ->assertOk()
            ->assertSee('Updated by Super Admin', false)
            ->assertSee('25%', false);

        $this->actingAs($branch)->get("/branch/commission-partners/{$untouched->id}/edit")
            ->assertOk()
            ->assertDontSee('Updated by Super Admin', false);
    }

    public function test_marking_as_read_clears_the_dashboard_panel_but_keeps_the_history(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 20);

        $this->actingAs($this->superAdmin())
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, ['commission_percentage' => 25]));

        $this->actingAs($branch)->patch('/branch/partner-updates/read')->assertRedirect();

        $this->assertNotNull(CommissionPartnerUpdate::sole()->read_at);

        $this->actingAs($branch)->get('/branch/dashboard')
            ->assertOk()
            ->assertDontSee('Updates from Super Admin', false);

        $this->actingAs($branch)->get("/branch/commission-partners/{$partner->id}/edit")
            ->assertSee('Updated by Super Admin', false);
    }

    public function test_one_note_can_be_marked_read_on_its_own(): void
    {
        $branch = $this->makeBranchManager(30);
        $a = $this->makeCommissionPartner($branch, 20);
        $b = $this->makeCommissionPartner($branch, 20);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put("/admin/commission-partners/{$a->id}", $this->payload($a, ['commission_percentage' => 21]));
        $this->actingAs($admin)->put("/admin/commission-partners/{$b->id}", $this->payload($b, ['commission_percentage' => 22]));

        $first = CommissionPartnerUpdate::where('commission_partner_id', $a->id)->sole();

        $this->actingAs($branch)->patch('/branch/partner-updates/read', ['id' => $first->id]);

        $this->assertNotNull($first->refresh()->read_at);
        $this->assertNull(CommissionPartnerUpdate::where('commission_partner_id', $b->id)->sole()->read_at);
    }

    public function test_another_branch_manager_neither_sees_nor_can_dismiss_the_note(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 20);
        $otherBranch = $this->makeBranchManager(30);

        $this->actingAs($this->superAdmin())
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, ['commission_percentage' => 25]));

        $this->actingAs($otherBranch)->get('/branch/dashboard')
            ->assertOk()
            ->assertDontSee('Updates from Super Admin', false);

        $note = CommissionPartnerUpdate::sole();
        $this->actingAs($otherBranch)->patch('/branch/partner-updates/read', ['id' => $note->id]);

        $this->assertNull($note->refresh()->read_at, 'another Branch Manager cannot mark it read');
    }

    public function test_the_changed_rate_is_what_the_next_activation_pays(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 20);

        $this->actingAs($this->superAdmin())
            ->put("/admin/commission-partners/{$partner->id}", $this->payload($partner, ['commission_percentage' => 25]));

        [, $microsite] = $this->makeVipMember($partner->refresh(), $this->makePlan(1000), $this->makeCity());
        $transaction = app(\App\Services\VipActivationService::class)->activate($microsite, $partner);

        $this->assertEquals(250.0, (float) $transaction->commission_partner_amount);
        $this->assertEquals(50.0, (float) $transaction->branch_manager_amount);
    }
}
