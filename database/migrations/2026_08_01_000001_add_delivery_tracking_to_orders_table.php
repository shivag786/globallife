<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Admin-editable estimated delivery date (defaults to placed_at + global
            // delivery days at order time; admin can override per order).
            $table->date('expected_delivery_date')->nullable()->after('placed_at');

            // Milestone timestamps, stamped as the order advances — power the
            // Amazon/Flipkart-style tracking timeline.
            $table->timestamp('processing_at')->nullable()->after('expected_delivery_date');
            $table->timestamp('dispatched_at')->nullable()->after('processing_at');
            $table->timestamp('delivered_at')->nullable()->after('dispatched_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['expected_delivery_date', 'processing_at', 'dispatched_at', 'delivered_at']);
        });
    }
};
