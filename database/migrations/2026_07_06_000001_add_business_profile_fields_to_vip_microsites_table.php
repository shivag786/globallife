<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_microsites', function (Blueprint $table) {
            // Basic Information
            $table->string('business_category')->nullable()->after('business_name');
            $table->string('business_sub_category')->nullable()->after('business_category');
            $table->string('owner_name')->nullable()->after('business_sub_category');
            $table->string('short_description')->nullable()->after('description');
            $table->unsignedSmallInteger('establishment_year')->nullable()->after('short_description');
            $table->string('gst_no')->nullable()->after('establishment_year');
            $table->string('pan_no')->nullable()->after('gst_no');
            $table->string('cin_no')->nullable()->after('pan_no');
            $table->string('logo_path')->nullable()->after('cin_no');
            $table->string('cover_banner_path')->nullable()->after('logo_path');
            $table->string('business_email')->nullable()->after('cover_banner_path');
            $table->string('phone_number')->nullable()->after('business_email');
            $table->string('alternate_number')->nullable()->after('phone_number');
            $table->string('whatsapp_number')->nullable()->after('alternate_number');
            $table->string('website_url')->nullable()->after('whatsapp_number');

            // Contact / Business Hours
            $table->string('address')->nullable()->after('website_url');
            $table->string('google_map_url')->nullable()->after('address');
            $table->json('business_hours')->nullable()->after('google_map_url');
            $table->json('holidays')->nullable()->after('business_hours');

            // Social Media
            $table->string('facebook_url')->nullable()->after('holidays');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('youtube_url')->nullable()->after('instagram_url');
            $table->string('linkedin_url')->nullable()->after('youtube_url');
            $table->string('twitter_url')->nullable()->after('linkedin_url');
            $table->string('telegram_url')->nullable()->after('twitter_url');
            $table->string('pinterest_url')->nullable()->after('telegram_url');

            // Dynamic module Display ON/OFF map, e.g. {"services": true, "products": false, ...}
            $table->json('module_visibility')->nullable()->after('pinterest_url');
        });
    }

    public function down(): void
    {
        Schema::table('vip_microsites', function (Blueprint $table) {
            $table->dropColumn([
                'business_category', 'business_sub_category', 'owner_name', 'short_description',
                'establishment_year', 'gst_no', 'pan_no', 'cin_no', 'logo_path', 'cover_banner_path',
                'business_email', 'phone_number', 'alternate_number', 'whatsapp_number', 'website_url',
                'address', 'google_map_url', 'business_hours', 'holidays',
                'facebook_url', 'instagram_url', 'youtube_url', 'linkedin_url', 'twitter_url', 'telegram_url', 'pinterest_url',
                'module_visibility',
            ]);
        });
    }
};
