<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The product-sale commission ledger (separate from VIP joining commission).
     * A row per beneficiary per order item. Earnings are created as `pending` when
     * the order is placed and flipped to `approved` (and credited to the wallet)
     * only when the order is delivered.
     */
    public function up(): void
    {
        Schema::create('commission_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('seller_microsite_id')->nullable()->constrained('vip_microsites')->nullOnDelete();
            $table->foreignId('beneficiary_id')->constrained('users')->cascadeOnDelete();

            $table->string('role');
            $table->decimal('base_amount', 10, 2);
            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('value', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['beneficiary_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_earnings');
    }
};
