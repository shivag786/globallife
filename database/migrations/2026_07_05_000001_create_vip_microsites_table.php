<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_microsites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();
            $table->foreignId('vip_plan_id')->constrained('vip_plans')->restrictOnDelete();
            $table->string('business_name');
            $table->string('business_slug');
            $table->text('description')->nullable();
            $table->string('secure_token', 12);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['city_id', 'business_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_microsites');
    }
};
