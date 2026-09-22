<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * VIP members' withdrawal requests against their product-commission wallet.
     *
     * Distinct from `commission_payouts`, which is the Super Admin settling a
     * Commission Partner's month. This one is member-initiated, one row per
     * request, and the wallet is only debited when an admin marks it paid —
     * payment itself happens outside the system (bank transfer), so the proof of
     * payment (UTR and/or a screenshot) lives here.
     */
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            // What their wallet held when they asked — keeps the request auditable
            // even after later earnings change the balance.
            $table->decimal('wallet_balance_at_request', 12, 2)->default(0);

            // Proof of the manual transfer. At least one of these is required
            // before a request can be marked paid; the member sees both.
            $table->string('utr_number', 120)->nullable();
            $table->string('payment_screenshot_path')->nullable();
            $table->text('admin_note')->nullable();

            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Drives the 24-hour cooldown lookup and the admin queue.
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
