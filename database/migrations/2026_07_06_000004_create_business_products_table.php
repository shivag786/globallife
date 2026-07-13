<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_microsite_id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->string('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->decimal('mrp', 10, 2)->nullable();
            $table->decimal('offer_price', 10, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('strike_price', 10, 2)->nullable();
            $table->boolean('show_pricing')->default(true);
            $table->string('sku')->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->string('brand')->nullable();
            $table->string('weight')->nullable();
            $table->string('color')->nullable();
            $table->string('variant')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['vip_microsite_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_products');
    }
};
