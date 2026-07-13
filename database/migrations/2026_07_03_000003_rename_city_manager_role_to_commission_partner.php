<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * In-place rename of the existing Spatie role row: every user currently holding
     * "city_manager" becomes "commission_partner" automatically, since model_has_roles
     * references the role by id, not name — zero data loss.
     */
    public function up(): void
    {
        DB::table('roles')->where('name', 'city_manager')->update(['name' => 'commission_partner']);
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'commission_partner')->update(['name' => 'city_manager']);
    }
};
