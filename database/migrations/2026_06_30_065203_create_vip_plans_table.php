<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vip_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('yearly_price', 10, 2);
            $table->decimal('joining_price', 10, 2)->default(0);
            $table->decimal('renewal_price', 10, 2);
            $table->json('features')->nullable();
            $table->json('highlight_features')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('upgrade_priority')->default(0);
            $table->unsignedInteger('display_order')->default(0);
            $table->unsignedInteger('microsite_limit')->default(1);
            $table->unsignedInteger('landing_page_limit')->default(0);
            $table->unsignedInteger('blog_limit')->default(0);
            $table->unsignedInteger('analytics_limit_days')->default(30);
            $table->unsignedInteger('storage_limit_mb')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vip_plans');
    }
};
