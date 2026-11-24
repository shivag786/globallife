<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The owner asked for the "Ready to Get Started? / View VIP Plans" call-to-action
 * box to come off the homepage. It is a Homepage Builder row, so it is switched
 * to inactive (not deleted) — an admin can turn it back on from the builder.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('home_sections')
            ->where('type', 'cta')
            ->where('cta_url', '/vip-plans')
            ->update(['status' => 'inactive']);
    }

    public function down(): void
    {
        DB::table('home_sections')
            ->where('type', 'cta')
            ->where('cta_url', '/vip-plans')
            ->update(['status' => 'active']);
    }
};
