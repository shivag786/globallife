<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records which gateway took the money and the Razorpay handles needed to
 * reconcile / refund the payment later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_gateway', 30)->nullable()->after('payment_method');
            $table->string('razorpay_order_id')->nullable()->after('payment_gateway');
            $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            $table->string('razorpay_signature')->nullable()->after('razorpay_payment_id');

            $table->index('razorpay_order_id');
            $table->index('razorpay_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['razorpay_order_id']);
            $table->dropIndex(['razorpay_payment_id']);
            $table->dropColumn(['payment_gateway', 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature']);
        });
    }
};
