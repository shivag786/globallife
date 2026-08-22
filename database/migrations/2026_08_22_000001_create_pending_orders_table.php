<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A checkout that has been priced and handed to Razorpay but not yet paid.
 *
 * The cart lives in the customer's session, which a server-to-server webhook can
 * never see — so everything the order needs is snapshotted here at the moment the
 * gateway order is created. Whoever confirms the payment first (the browser or the
 * webhook) turns this row into a real order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_orders', function (Blueprint $table) {
            $table->id();
            $table->string('razorpay_order_id')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Delivery address snapshot — the address row may change or be deleted
            // between payment and confirmation.
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->text('address');
            $table->string('city', 120);
            $table->string('state', 120);
            $table->string('pincode', 12);
            $table->text('delivery_notes')->nullable();

            // Line items as priced when the customer was charged.
            $table->json('items');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('INR');

            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('failure_reason')->nullable();
            // Set once the payment is confirmed and the order exists.
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('razorpay_payment_id')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_orders');
    }
};
