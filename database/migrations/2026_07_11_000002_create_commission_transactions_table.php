<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_microsite_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vip_plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('commission_partner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('branch_manager_id')->constrained('users')->restrictOnDelete();
            $table->decimal('package_amount', 10, 2);
            $table->decimal('commission_partner_percentage', 5, 2);
            $table->decimal('branch_manager_percentage', 5, 2);
            $table->decimal('commission_partner_amount', 10, 2);
            $table->decimal('branch_manager_amount', 10, 2);
            $table->decimal('company_amount', 10, 2);
            $table->foreignId('activated_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('activated_at');
            $table->timestamps();

            $table->index(['commission_partner_id']);
            $table->index(['branch_manager_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_transactions');
    }
};
