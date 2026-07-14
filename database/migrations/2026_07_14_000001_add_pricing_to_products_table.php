<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Selling price shown to customers. Nullable so a product can be
            // "enquire for price" until a price is set.
            $table->decimal('price', 10, 2)->nullable()->after('badge');
            // Optional MRP / list price — when higher than price, the UI shows
            // a strikethrough and the computed discount percentage.
            $table->decimal('mrp', 10, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'mrp']);
        });
    }
};
