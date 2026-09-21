<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An audit trail of every renewal decision a Commission Partner made on an
     * expired VIP plan. Approving extends `vip_microsites.plan_expires_at`;
     * rejecting only records the refusal and leaves the microsite expired.
     */
    public function up(): void
    {
        Schema::create('vip_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_microsite_id')->constrained('vip_microsites')->cascadeOnDelete();
            $table->foreignId('vip_plan_id')->constrained('vip_plans')->restrictOnDelete();
            $table->enum('decision', ['approved', 'rejected']);
            // Snapshot of the plan's renewal_price at the time of the decision.
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamp('previous_expires_at')->nullable();
            $table->timestamp('new_expires_at')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('decided_by')->constrained('users')->restrictOnDelete();
            // Needs an explicit default: it is not the first timestamp column, so
            // MySQL/MariaDB will not implicitly supply one for a NOT NULL timestamp.
            $table->timestamp('decided_at')->useCurrent();
            $table->timestamps();

            $table->index(['vip_microsite_id', 'decided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_renewals');
    }
};
