<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which Super-Admin catalog products a VIP member has chosen to sell on their
     * storefront. The VIP only controls visibility, featured, and order — never
     * price, description, commission, reviews, ratings, or benefits.
     */
    public function up(): void
    {
        Schema::create('vip_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_microsite_id')->constrained('vip_microsites')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['vip_microsite_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_products');
    }
};
