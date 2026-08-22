<?php

namespace App\Services;

use App\Models\CommissionEarning;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PendingOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PaymentSimulator $payment,
        private readonly ProductCommissionService $commission,
        private readonly DeliveryService $delivery,
    ) {
    }

    /**
     * Place an order from the current cart for an authenticated customer. Returns
     * null when payment fails (nothing is persisted) so the caller can send the
     * customer back to retry.
     *
     * @param  array<string, mixed>  $data  validated checkout data (address snapshot + payment)
     */
    public function placeFromCart(array $data, User $customer): ?Order
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) {
            return null;
        }

        $method = $data['payment_method'];
        $outcome = $data['payment_outcome'] ?? 'success';
        $gateway = $data['payment_gateway'] ?? null;

        // A real gateway has already taken (and verified) the money before we get
        // here — the simulator only stands in for the test / COD paths.
        if ($gateway !== 'razorpay' && ! $this->payment->charge($method, $outcome)) {
            return null;
        }

        $totals = $this->cart->totals();

        $order = $this->persistOrder($data, $customer, $this->normaliseCartItems($items), $totals, $method, $gateway);

        $this->cart->clear();

        return $order->load('items');
    }

    /**
     * Confirm a Razorpay checkout that was snapshotted before payment. The money is
     * already captured, so this never consults the simulator and never touches the
     * session cart — the webhook runs without either.
     *
     * @param  array<string, mixed>  $payment  razorpay_order_id / _payment_id / _signature
     */
    public function placeFromPending(PendingOrder $pending, array $payment): Order
    {
        $totals = [
            'subtotal' => (float) $pending->subtotal,
            'shipping' => (float) $pending->shipping,
            'total' => (float) $pending->total,
        ];

        return $this->persistOrder(
            $pending->deliveryData() + $payment,
            $pending->user,
            $this->rehydrateSnapshot($pending->items ?? []),
            $totals,
            'online',
            'razorpay',
        )->load('items');
    }

    /**
     * Cart rows carry live Product / VipMicrosite models; flatten them to the shape
     * persistOrder() works with.
     *
     * @param  Collection<int, array<string, mixed>>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function normaliseCartItems(Collection $items): Collection
    {
        return $items->map(fn (array $item) => [
            'product_id' => $item['product']->id,
            'product_name' => $item['product']->name,
            'product_sku' => $item['product']->sku,
            'seller_id' => $item['seller_id'],
            'unit_price' => $item['unit_price'],
            'quantity' => $item['quantity'],
            'line_total' => $item['line_total'],
            'product' => $item['product'],
            'seller' => $item['seller'],
        ]);
    }

    /**
     * Rebuild priced lines from a PendingOrder snapshot. The stored name/sku/price
     * always win — the customer has already paid them, so a product that has since
     * been edited, deactivated or deleted must not change or drop the line. The
     * live models are looked up only to work out commission.
     *
     * @param  array<int, array<string, mixed>>  $snapshot
     * @return Collection<int, array<string, mixed>>
     */
    private function rehydrateSnapshot(array $snapshot): Collection
    {
        $lines = collect($snapshot);

        $products = Product::whereIn('id', $lines->pluck('product_id')->filter()->unique())
            ->get()->keyBy('id');

        $sellerIds = $lines->pluck('seller_id')->filter()->unique();
        $sellers = $sellerIds->isNotEmpty()
            ? VipMicrosite::whereIn('id', $sellerIds)->get()->keyBy('id')
            : collect();

        return $lines->map(fn (array $line) => $line + [
            'product' => $products->get($line['product_id']),
            'seller' => $line['seller_id'] ? $sellers->get($line['seller_id']) : null,
        ]);
    }

    /**
     * Write the order, its items and each beneficiary's pending commission.
     *
     * @param  array<string, mixed>  $data
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  array<string, float>  $totals
     */
    private function persistOrder(array $data, User $customer, Collection $items, array $totals, string $method, ?string $gateway): Order
    {
        return DB::transaction(function () use ($items, $totals, $data, $customer, $method, $gateway) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $customer->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $customer->email,
                'customer_phone' => $data['customer_phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'delivery_notes' => $data['delivery_notes'] ?? null,
                'payment_method' => $method,
                'payment_gateway' => $gateway,
                'razorpay_order_id' => $data['razorpay_order_id'] ?? null,
                'razorpay_payment_id' => $data['razorpay_payment_id'] ?? null,
                'razorpay_signature' => $data['razorpay_signature'] ?? null,
                'payment_status' => $method === 'cod' ? 'pending' : 'paid',
                'status' => 'confirmed',
                'subtotal' => $totals['subtotal'],
                'shipping' => $totals['shipping'],
                'total' => $totals['total'],
                'placed_at' => now(),
                // Estimated delivery: order date + the global delivery window.
                // Admin can override this per order later.
                'expected_delivery_date' => now()->addDays($this->delivery->days())->toDateString(),
            ]);

            foreach ($items as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'seller_microsite_id' => $item['seller_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total'],
                ]);

                // No live product (deleted since payment) means no commission rule
                // to price against; the paid line itself is still recorded above.
                if ($item['product'] instanceof Product) {
                    $this->recordPendingEarnings($order, $orderItem, $item['product'], $item['seller']);
                }
            }

            return $order;
        });
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

        // Stamp the milestone the first time the order reaches it, so the tracking
        // timeline shows the real date/time of each step.
        $stamp = match ($status) {
            'processing' => $order->processing_at ? [] : ['processing_at' => now()],
            'dispatched' => $order->dispatched_at ? [] : ['dispatched_at' => now()],
            default => [],
        };

        $order->update(['status' => $status] + $stamp);
    }

    public function markDelivered(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'delivered',
                'delivered_at' => $order->delivered_at ?? now(),
            ]);

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
