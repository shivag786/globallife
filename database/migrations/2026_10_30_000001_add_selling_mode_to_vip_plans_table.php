<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How a package's members may sell their OWN products (`business_products`):
 *
 *  - catalogue: the products are shown as a catalogue and each card carries an
 *    "Order on WhatsApp" button to the business. No cart, no payment.
 *  - direct:    add to cart, delivery address, Razorpay / COD through the
 *    company's checkout. The business delivers; the company settles the money.
 *
 * Catalogue is the default because it is how every page behaved before this
 * switch existed (products were display-only), so nobody's page starts taking
 * orders without an admin deciding it should.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_plans', function (Blueprint $table) {
            $table->enum('selling_mode', ['catalogue', 'direct'])->default('catalogue')->after('service_limit');
        });
    }

    public function down(): void
    {
        Schema::table('vip_plans', function (Blueprint $table) {
            $table->dropColumn('selling_mode');
        });
    }
};
