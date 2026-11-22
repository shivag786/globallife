<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * A chatbot / website enquiry becomes a NEW lead: counted in red on the admin
 * sidebar, listed first in red on the dashboard and the leads list, and
 * picked up by the alert poll (sound + toast) until someone opens it.
 */
class NewLeadAlertTest extends TestCase
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

    private function chatbotLead(string $name = 'Chat Visitor'): Lead
    {
        $this->postJson('/enquiry', [
            'name' => $name, 'phone' => '9876543210', 'email' => 'visitor@example.com',
            'city' => 'Jhansi', 'source' => 'chatbot', 'message' => 'Interested in: Something else',
        ])->assertOk();

        return Lead::latest('id')->firstOrFail();
    }

    public function test_the_chatbot_enquiry_is_saved_as_a_new_lead_with_mobile(): void
    {
        $lead = $this->chatbotLead();

        $this->assertSame('9876543210', $lead->phone);
        $this->assertSame('chatbot', $lead->source);
        $this->assertTrue($lead->isNew());
    }

    public function test_a_bad_email_is_reported_back_to_the_chatbot(): void
    {
        $this->postJson('/enquiry', ['name' => 'X Y', 'email' => 'not-an-email', 'source' => 'chatbot'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_new_lead_shows_red_on_sidebar_dashboard_and_list(): void
    {
        $this->chatbotLead('Rahul Verma');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('New Leads')
            ->assertSee('Rahul Verma')
            ->assertSee('data-lead-badge', false)
            ->assertSee('data-lead-alert', false);

        $this->actingAs($admin)->get('/admin/leads')->assertOk()->assertSee('NEW')->assertSee('bg-danger-subtle', false);
    }

    public function test_the_poll_reports_leads_since_the_last_one_seen(): void
    {
        $admin = $this->superAdmin();
        $before = (int) Lead::max('id');

        $this->chatbotLead('Neha');

        $this->actingAs($admin)->getJson('/admin/leads/poll?since='.$before)
            ->assertOk()
            ->assertJson(['new' => 1, 'unseen' => 1, 'names' => ['Neha']]);
    }

    public function test_opening_a_lead_clears_it_from_new(): void
    {
        $lead = $this->chatbotLead();
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get("/admin/leads/{$lead->id}")->assertOk();

        $this->assertFalse($lead->fresh()->isNew());
        $this->actingAs($admin)->get('/admin/dashboard')->assertDontSee('id="new-leads"', false);
        $this->actingAs($admin)->getJson('/admin/leads/poll?since=0')->assertJson(['unseen' => 0]);
    }

    public function test_sub_admins_get_the_alert_only_with_the_leads_module(): void
    {
        $this->chatbotLead('Amit');

        $without = $this->makeSubAdmin();
        $this->actingAs($without)->getJson('/admin/leads/poll?since=0')->assertForbidden();
        $this->actingAs($without)->get('/admin/dashboard')->assertOk()
            ->assertDontSee('data-lead-alert', false)->assertDontSee('Amit');

        $with = $this->makeSubAdmin(['leads']);
        $this->actingAs($with)->getJson('/admin/leads/poll?since=0')->assertOk()->assertJson(['unseen' => 1]);
        $this->actingAs($with)->get('/admin/dashboard')->assertOk()
            ->assertSee('data-lead-alert', false)->assertSee('Amit');
    }

    public function test_the_chatbot_starts_with_buttons_and_a_locked_text_box(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="chatbot-input"[^>]*disabled/', $html);
        $this->assertStringContainsString('Please choose an option above', $html);
    }
}
