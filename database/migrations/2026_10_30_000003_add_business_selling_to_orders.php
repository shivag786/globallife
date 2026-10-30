<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Direct selling of a business's OWN products through the company checkout.
 *
 *  - `order_items.business_product_id` — the line is one of the business's own
 *    products rather than a company catalogue product (`product_id` stays NULL).
 *  - `orders.fulfilled_by_microsite_id` — the business that delivers this order.
 *    Such an order holds only that business's products (the cart enforces it).
 *  - `business_settlements` — the company passing the money it collected online
 *    on to the business, with the UTR / screenshot of the transfer. The orders it
 *    covered point back at it through `orders.business_settlement_id`, so an
 *    order can never be settled twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_microsite_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('orders_count')->default(0);
            $table->string('utr_number', 120)->nullable();
            $table->string('payment_screenshot_path')->nullable();
            $table->string('note', 500)->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('fulfilled_by_microsite_id')->nullable()->after('user_id')
                ->constrained('vip_microsites')->nullOnDelete();
            $table->foreignId('business_settlement_id')->nullable()->after('commission_credited')
                ->constrained('business_settlements')->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('business_product_id')->nullable()->after('product_id')
                ->constrained('business_products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_product_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_settlement_id');
            $table->dropConstrainedForeignId('fulfilled_by_microsite_id');
        });

        Schema::dropIfExists('business_settlements');
    }
};
