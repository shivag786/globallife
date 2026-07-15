<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expands the existing products table into a full commerce catalog record.
     * All columns are nullable / defaulted so the current storefront keeps working
     * unchanged; the legacy string `category` column is retained for back-compat
     * while `category_id` becomes the source of truth going forward.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('category')->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands')->nullOnDelete();
            $table->string('sku')->nullable()->unique()->after('brand_id');
            // Discounted price the customer actually pays; falls back to `price` when null.
            $table->decimal('offer_price', 10, 2)->nullable()->after('mrp');
            $table->unsignedInteger('stock')->default(0)->after('offer_price');
            // Aggregate rating maintained by the review system (denormalized for fast reads).
            $table->decimal('rating', 3, 2)->default(0)->after('stock');
            $table->unsignedInteger('review_count')->default(0)->after('rating');
            $table->json('gallery')->nullable()->after('main_image');
            $table->longText('ingredients')->nullable()->after('long_description');
            $table->longText('usage_instructions')->nullable()->after('ingredients');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('brand_id');
            $table->dropColumn(['sku', 'offer_price', 'stock', 'rating', 'review_count', 'gallery', 'ingredients', 'usage_instructions']);
        });
    }
};
