<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records a super-admin payout to a beneficiary for one calendar month.
     * A month can be paid more than once (a later delivery approves more earnings
     * inside an already-settled month), so the payable amount is always
     * "earned in that month" minus "already paid out for that month".
     */
    public function up(): void
    {
        Schema::create('commission_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Calendar month this payout settles, as YYYY-MM.
            $table->char('period', 7);
            // Product-sale share (sits in the wallet) vs VIP-activation share (ledger only).
            $table->decimal('product_amount', 12, 2)->default(0);
            $table->decimal('vip_amount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            // How much was actually taken off the wallet balance (never below zero).
            $table->decimal('wallet_debited', 12, 2)->default(0);
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('paid_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index(['user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_payouts');
    }
};
