<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal, address, nominee and KYC details for Branch Managers and
 * Commission Partners, one row per user.
 *
 * Kept off `users` on purpose: it is optional, only some roles have it, and it
 * holds identity data that should not ride along with every user query.
 *
 * Aadhaar and PAN numbers are stored ENCRYPTED (Laravel's `encrypted` cast,
 * hence `text`), and the photo / document scans live on the private `kyc`
 * disk -- never under a public URL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Personal
            $table->string('salutation', 10)->nullable();
            $table->string('relation_type', 5)->nullable();
            $table->string('relative_name', 150)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group', 3)->nullable();

            // Contact
            $table->string('alternate_mobile', 10)->nullable();

            // Address
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 6)->nullable();

            // Nominee
            $table->string('nominee_name', 150)->nullable();
            $table->string('nominee_relation', 30)->nullable();
            $table->date('nominee_date_of_birth')->nullable();
            $table->string('nominee_mobile', 10)->nullable();

            // KYC -- numbers encrypted at rest, files on the private kyc disk.
            $table->text('aadhaar_number')->nullable();
            $table->text('pan_number')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('aadhaar_file_path')->nullable();
            $table->string('pan_file_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
