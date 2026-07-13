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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->text('message')->nullable();
            $table->enum('source', ['contact_page', 'homepage', 'chatbot'])->default('contact_page');
            $table->foreignId('interested_plan_id')->nullable()->constrained('vip_plans')->nullOnDelete();
            $table->enum('status', ['new', 'contacted', 'converted', 'closed'])->default('new');
            $table->foreignId('assigned_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
