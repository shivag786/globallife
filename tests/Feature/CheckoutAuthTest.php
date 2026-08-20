<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Checkout now requires an account: guests register (setting their own password)
 * or log in, then pick a saved delivery address before paying.
 */
class CheckoutAuthTest extends TestCase
{
    use RefreshDatabase;

    private function cartWithItem(): Product
    {
        $product = Product::create([
            'name' => 'Wellness Box', 'slug' => 'wellness-'.uniqid(),
            'short_description' => 'x', 'price' => 500, 'status' => 'active',
        ]);
        app(CartService::class)->add($product->id, null, 1);

        return $product;
    }

    private function address(User $user): Address
    {
        return $user->addresses()->create([
            'name' => 'Jane', 'phone' => '9999999999', 'address' => '12 Test St',
            'city' => 'Jhansi', 'state' => 'UP', 'pincode' => '284001', 'is_default' => true,
        ]);
    }

    public function test_guest_sees_identify_step_not_payment(): void
    {
        $this->cartWithItem();

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('New customer')
            ->assertSee(route('checkout.register'), false)
            ->assertSee('Delivery by')
            ->assertDontSee('Place Order');
    }

    public function test_register_at_checkout_creates_customer_and_logs_in(): void
    {
        $this->cartWithItem();

        $this->post(route('checkout.register'), [
            'name' => 'New Buyer', 'email' => 'new@example.com', 'mobile' => '9999999999',
            'password' => 'secret1234', 'password_confirmation' => 'secret1234',
        ])->assertRedirect(route('checkout.index'));

        $this->assertAuthenticated();
        $user = User::where('email', 'new@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame('active', $user->status);
    }

    /**
     * An AJAX register/login must answer validation failures with 422 JSON.
     * When it redirected instead, fetch() followed the redirect, saw 200 OK and
     * reloaded the page - the customer got a blank form and no error at all.
     */
    public function test_ajax_register_returns_json_validation_errors_not_a_redirect(): void
    {
        $this->cartWithItem();

        $response = $this->postJson(route('checkout.register'), [
            'name' => '', 'email' => 'not-an-email', 'mobile' => '',
            'password' => 'short', 'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'mobile', 'password']);

        $this->assertGuest();
    }

    public function test_ajax_register_with_taken_email_returns_json_error(): void
    {
        User::factory()->create(['status' => 'active', 'email' => 'taken@example.com']);
        $this->cartWithItem();

        $this->postJson(route('checkout.register'), [
            'name' => 'Someone', 'email' => 'taken@example.com', 'mobile' => '9999999999',
            'password' => 'secret1234', 'password_confirmation' => 'secret1234',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_ajax_login_returns_json_error_for_bad_credentials(): void
    {
        User::factory()->create([
            'status' => 'active', 'email' => 'real@example.com',
            'password' => Hash::make('secret1234'),
        ]);
        $this->cartWithItem();

        $this->postJson(route('checkout.login'), [
            'email' => 'real@example.com', 'password' => 'wrong-password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertGuest();
    }

    public function test_register_with_existing_email_is_rejected(): void
    {
        User::factory()->create(['status' => 'active', 'email' => 'taken@example.com']);
        $this->cartWithItem();

        $this->post(route('checkout.register'), [
            'name' => 'Someone Else', 'email' => 'taken@example.com', 'mobile' => '9999999999',
            'password' => 'secret1234', 'password_confirmation' => 'secret1234',
        ])->assertSessionHasErrors('email');

        // No duplicate account created, and they were not logged in.
        $this->assertSame(1, User::where('email', 'taken@example.com')->count());
        $this->assertGuest();
    }

    public function test_login_at_checkout_authenticates(): void
    {
        $user = User::factory()->create(['status' => 'active', 'password' => Hash::make('secret1234')]);
        $this->cartWithItem();

        $this->post(route('checkout.login'), ['email' => $user->email, 'password' => 'secret1234'])
            ->assertRedirect(route('checkout.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_bad_credentials(): void
    {
        $user = User::factory()->create(['status' => 'active', 'password' => Hash::make('secret1234')]);

        $this->post(route('checkout.login'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_first_saved_address_becomes_default(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user)->post(route('account.addresses.store'), [
            'name' => 'Jane', 'phone' => '9999999999', 'address' => '12 Test St',
            'city' => 'Jhansi', 'state' => 'UP', 'pincode' => '284001', 'redirect_to' => 'checkout',
        ])->assertRedirect(route('checkout.index'));

        $address = $user->addresses()->first();
        $this->assertNotNull($address);
        $this->assertTrue($address->is_default);
    }

    public function test_authenticated_checkout_shows_addresses_and_place_order(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->address($user);
        $this->cartWithItem();

        $this->actingAs($user)->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Deliver to')
            ->assertSee('Place Order')
            ->assertSee('Jhansi')
            ->assertSee('Delivery by')
            ->assertSee('Payment Method');
    }

    public function test_authenticated_customer_places_order_with_saved_address(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $address = $this->address($user);
        $this->cartWithItem();

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_choice' => 'cod',
        ])->assertRedirectContains('/checkout/confirmation/');

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame($user->email, $order->customer_email);
        $this->assertSame('Jhansi', $order->city);
        $this->assertSame('284001', $order->pincode);

        // The confirmation page shows the delivery date.
        $this->actingAs($user)->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Arriving by');
    }

    public function test_guest_cannot_place_order(): void
    {
        $this->cartWithItem();

        $this->post(route('checkout.store'), ['address_id' => 1, 'payment_choice' => 'cod'])
            ->assertRedirect(route('checkout.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_rejects_another_users_address(): void
    {
        $owner = User::factory()->create(['status' => 'active']);
        $address = $this->address($owner);

        $buyer = User::factory()->create(['status' => 'active']);
        $this->cartWithItem();

        $this->actingAs($buyer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_choice' => 'cod',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }
}
