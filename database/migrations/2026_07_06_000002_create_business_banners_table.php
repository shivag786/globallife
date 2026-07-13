<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_microsite_id')->constrained()->cascadeOnDelete();
            $table->enum('device', ['desktop', 'mobile']);
            $table->string('image_path');
            $table->string('heading')->nullable();
            $table->string('subheading')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_banners');
    }
};
