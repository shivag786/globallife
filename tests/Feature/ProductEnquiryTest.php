<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * A product enquiry is about the product whose page it was sent from.
 *
 * The product used to be known only from the free-text message, the form's
 * old input was global, and a failed submission went "back" to whatever page
 * the session last recorded. Together that let one product's message appear in
 * another product's enquiry box -- and get saved against it.
 */
class ProductEnquiryTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private function product(string $name): Product
    {
        return Product::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'short_description' => 'x',
            'price' => 499,
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Product $product, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Asha Verma',
            'email' => 'asha@example.com',
            'phone' => '9876543210',
            'message' => "I'm interested in ordering: {$product->name}.",
            'source' => 'product',
            'product_id' => $product->id,
            'enquiry_context' => 'product:product:'.$product->id,
            'return_to' => '/products/'.$product->slug,
        ], $overrides);
    }

    public function test_the_form_on_a_product_page_carries_that_product(): void
    {
        $lavender = $this->product('Lavender Calm EDP');

        $response = $this->get('/products/'.$lavender->slug);

        $response->assertOk();
        $response->assertSee('name="product_id" value="'.$lavender->id.'"', false);
        $response->assertSee("interested in ordering: Lavender Calm EDP", false);
    }

    public function test_the_lead_records_the_product_it_was_sent_from(): void
    {
        $lavender = $this->product('Lavender Calm EDP');

        $this->post('/enquiry', $this->payload($lavender))->assertRedirect();

        $lead = Lead::sole();

        $this->assertSame($lavender->id, $lead->product_id);
        $this->assertSame('Lavender Calm EDP', $lead->product->name);
    }

    public function test_a_success_returns_to_the_page_submitted_from_not_the_last_page_visited(): void
    {
        $lavender = $this->product('Lavender Calm EDP');
        $prowell = $this->product('Family Prowell Nutrition');

        // The session's "previous page" is Prowell (another tab, say)...
        $this->get('/products/'.$prowell->slug);

        // ...but the enquiry was sent from Lavender's page.
        $this->post('/enquiry', $this->payload($lavender))
            ->assertRedirect(url('/products/'.$lavender->slug));
    }

    public function test_a_failed_submission_returns_to_the_same_product_with_its_own_message(): void
    {
        $lavender = $this->product('Lavender Calm EDP');
        $prowell = $this->product('Family Prowell Nutrition');

        $this->get('/products/'.$prowell->slug);

        $this->post('/enquiry', $this->payload($lavender, ['email' => 'not-an-email']))
            ->assertRedirect(url('/products/'.$lavender->slug).'#enquire')
            ->assertSessionHasErrors('email');

        // Back on Lavender: the typed message is restored there.
        $this->get('/products/'.$lavender->slug)
            ->assertSee('not-an-email', false)
            ->assertSee("interested in ordering: Lavender Calm EDP", false);

        $this->assertSame(0, Lead::count());
    }

    public function test_one_products_old_input_never_fills_another_products_form(): void
    {
        $lavender = $this->product('Lavender Calm EDP');
        $prowell = $this->product('Family Prowell Nutrition');

        // Lavender's failed submission flashes its input...
        $this->post('/enquiry', $this->payload($lavender, [
            'email' => 'not-an-email',
            'return_to' => '/products/'.$prowell->slug,   // even if it lands on Prowell
        ]));

        // ...but Prowell's form shows Prowell's message, not Lavender's.
        $page = $this->get('/products/'.$prowell->slug);

        $page->assertOk();
        $page->assertSee("interested in ordering: Family Prowell Nutrition", false);
        $page->assertDontSee("interested in ordering: Lavender Calm EDP", false);
        $page->assertDontSee('not-an-email', false);
    }

    public function test_return_to_cannot_send_visitors_to_another_site(): void
    {
        $lavender = $this->product('Lavender Calm EDP');

        foreach (['https://evil.example/phish', '//evil.example/phish', 'javascript:alert(1)'] as $target) {
            $response = $this->post('/enquiry', $this->payload($lavender, ['return_to' => $target]));

            $this->assertStringNotContainsString('evil.example', (string) $response->headers->get('Location'));
            $this->assertStringNotContainsString('javascript:', (string) $response->headers->get('Location'));
        }
    }

    public function test_a_product_id_is_only_kept_on_a_product_enquiry(): void
    {
        $lavender = $this->product('Lavender Calm EDP');

        $this->post('/enquiry', $this->payload($lavender, ['source' => 'contact_page']))->assertRedirect();

        $this->assertNull(Lead::sole()->product_id);
    }

    public function test_bookkeeping_fields_are_not_stored_on_the_lead(): void
    {
        $lavender = $this->product('Lavender Calm EDP');

        $this->post('/enquiry', $this->payload($lavender))->assertRedirect();

        $attributes = Lead::sole()->getAttributes();

        $this->assertArrayNotHasKey('enquiry_context', $attributes);
        $this->assertArrayNotHasKey('return_to', $attributes);
    }

    public function test_admin_sees_the_recorded_product(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('super_admin');

        $lavender = $this->product('Lavender Calm EDP');

        // Even if the visitor rewrote the message to name something else, the
        // recorded product is what admin sees as the product.
        $this->post('/enquiry', $this->payload($lavender, ['message' => 'Actually tell me about something else']));
        $lead = Lead::sole();

        $this->actingAs($admin)->get('/admin/leads')
            ->assertOk()
            ->assertSee('Lavender Calm EDP', false);

        $this->actingAs($admin)->get("/admin/leads/{$lead->id}")
            ->assertOk()
            ->assertSee('Product', false)
            ->assertSee('Lavender Calm EDP', false);
    }
}
