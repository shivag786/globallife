<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Record which catalogue product a product-page enquiry is about.
 *
 * Until now the only trace was the free-text message ("I'm interested in
 * ordering: X"), so anything that put the wrong text in that box -- a refill
 * from another page's failed submission, a visitor editing it -- put the wrong
 * product on the lead. The product id is now posted by the product page itself.
 *
 * Nullable, and set to NULL if the product is ever deleted: the lead is still a
 * real enquiry worth keeping.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('vip_microsite_id')
                ->constrained('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
