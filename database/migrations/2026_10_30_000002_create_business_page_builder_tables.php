<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Storage for the VIP page builder.
 *
 *  - `vip_microsites.page_layout` holds the section order, each section's
 *    editable heading, and the "highlights" counters (label + number). It is
 *    presentation only, so JSON on the microsite rather than its own tables.
 *  - `business_sections` / `business_section_cards` are the owner's own card
 *    sections ("Meet Our Doctors", "Facilities", ...), each card with an image,
 *    short and long description and an optional button.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_microsites', function (Blueprint $table) {
            $table->json('page_layout')->nullable()->after('module_visibility');
        });

        Schema::create('business_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_microsite_id')->constrained()->cascadeOnDelete();
            $table->string('eyebrow', 80)->nullable();
            $table->string('title', 150);
            $table->string('subtitle', 255)->nullable();
            // cards = image on top; people = round photo (doctors, team).
            $table->enum('style', ['cards', 'people'])->default('cards');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('business_section_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_section_id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->string('title', 150);
            $table->string('subtitle', 150)->nullable();
            $table->string('short_description', 300)->nullable();
            $table->text('long_description')->nullable();
            $table->string('button_label', 40)->nullable();
            $table->string('button_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_section_cards');
        Schema::dropIfExists('business_sections');

        Schema::table('vip_microsites', function (Blueprint $table) {
            $table->dropColumn('page_layout');
        });
    }
};
