<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * When an admin first opened a lead. NULL = a new, unseen lead: it is counted
 * on the sidebar badge, rings the admin alert and is listed in red on the
 * dashboard until someone opens it.
 *
 * Existing leads are marked seen so the badge starts from zero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('seen_at')->nullable()->after('status');
            $table->index('seen_at');
        });

        DB::table('leads')->update(['seen_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['seen_at']);
            $table->dropColumn('seen_at');
        });
    }
};
