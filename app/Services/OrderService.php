<?php

namespace App\Services;

use App\Models\CommissionEarning;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class OrderService
{
    /** Plain password generated for a brand-new customer account (for the welcome email). */
    public ?string $generatedPassword = null;

    public bool $newAccount = false;

    public function __construct(
        private readonly CartService $cart,
        private readonly PaymentSimulator $payment,
        private readonly ProductCommissionService $commission,
    ) {
    }

    /**
     * Place an order from the current cart. Returns null when payment fails
     * (nothing is persisted) so the caller can send the customer back to retry.
     *
     * @param  array<string, mixed>  $data  validated checkout data
     */
    public function placeFromCart(array $data, ?User $authUser): ?Order
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) {
            return null;
        }

        $method = $data['payment_method'];
        $outcome = $data['payment_outcome'] ?? 'success';

        if (! $this->payment->charge($method, $outcome)) {
            return null;
        }

        $totals = $this->cart->totals();

        $order = DB::transaction(function () use ($items, $totals, $data, $authUser, $method) {
            $customer = $this->resolveCustomer($data, $authUser);

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $customer?->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'delivery_notes' => $data['delivery_notes'] ?? null,
                'payment_method' => $method,
                'payment_status' => $method === 'cod' ? 'pending' : 'paid',
                'status' => 'confirmed',
                'subtotal' => $totals['subtotal'],
                'shipping' => $totals['shipping'],
                'total' => $totals['total'],
                'placed_at' => now(),
            ]);

            foreach ($items as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product']->id,
                    'seller_microsite_id' => $item['seller_id'],
                    'product_name' => $item['product']->name,
                    'product_sku' => $item['product']->sku,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total'],
                ]);

                $this->recordPendingEarnings($order, $orderItem, $item['product'], $item['seller']);
            }

            return $order;
        });

        $this->cart->clear();

        return $order->load('items');
    }

    /**
     * Resolve the customer: the logged-in user, an existing account by email, or a
     * freshly created customer account (whose plain password is captured for the email).
     */
    private function resolveCustomer(array $data, ?User $authUser): ?User
    {
        if ($authUser) {
            return $authUser;
        }

        $existing = User::where('email', $data['customer_email'])->first();
        if ($existing) {
            return $existing;
        }

        $password = Str::password(10, letters: true, numbers: true, symbols: false);
        $this->generatedPassword = $password;
        $this->newAccount = true;

        $user = User::create([
            'name' => $data['customer_name'],
            'email' => $data['customer_email'],
            'password' => Hash::make($password),
            'mobile' => $data['customer_phone'],
            'status' => 'active',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        Role::findOrCreate('customer', 'web');
        $user->assignRole('customer');

        return $user;
    }

    /**
     * Record each beneficiary's commission as PENDING at order time. It is only
     * approved + credited to wallets when the order is delivered.
     */
    private function recordPendingEarnings(Order $order, OrderItem $orderItem, Product $product, ?VipMicrosite $microsite): void
    {
        if (! $microsite) {
            return;
        }

        $seller = $microsite->user;
        if (! $seller) {
            return;
        }

        $split = $this->commission->calculateSplit($product, $seller, (float) $orderItem->line_total);

        foreach ($split['lines'] as $line) {
            CommissionEarning::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'product_id' => $product->id,
                'seller_microsite_id' => $microsite->id,
                'beneficiary_id' => $line['user_id'],
                'role' => $line['role'],
                'base_amount' => $orderItem->line_total,
                'type' => $line['type'],
                'value' => $line['value'],
                'amount' => $line['amount'],
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Admin status change. Reaching "delivered" approves the pending commission and
     * credits every beneficiary's wallet exactly once.
     */
    public function updateStatus(Order $order, string $status): void
    {
        if ($status === 'delivered') {
            $this->markDelivered($order);

            return;
        }

        $order->update(['status' => $status]);
    }

    public function markDelivered(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update(['status' => 'delivered']);

            if ($order->commission_credited) {
                return;
            }

            foreach ($order->earnings()->where('status', 'pending')->get() as $earning) {
                $earning->update(['status' => 'approved', 'approved_at' => now()]);
                Wallet::firstOrCreate(['user_id' => $earning->beneficiary_id])->increment('balance', $earning->amount);
            }

            $order->update([
                'commission_credited' => true,
                'payment_status' => $order->payment_method === 'cod' ? 'paid' : $order->payment_status,
            ]);
        });
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'GL'.now()->format('ymd').strtoupper(Str::random(5));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
