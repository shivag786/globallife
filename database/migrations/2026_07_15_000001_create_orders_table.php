<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            // The customer account (auto-created at first purchase). Nullable so a
            // deleted account never orphans the order history.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 30);
            $table->text('address');
            $table->string('city', 120);
            $table->string('state', 120);
            $table->string('pincode', 12);
            $table->text('delivery_notes')->nullable();

            $table->enum('payment_method', ['online', 'cod']);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('status', ['pending', 'confirmed', 'processing', 'dispatched', 'delivered', 'cancelled', 'refunded'])->default('pending');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            // Guards against double-crediting commission when an order is delivered.
            $table->boolean('commission_credited')->default(false);
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
