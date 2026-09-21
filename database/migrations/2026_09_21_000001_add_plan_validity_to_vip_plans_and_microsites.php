<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gives a VIP plan a validity window and stamps each microsite with the date
     * its plan runs out. Until now the "renews in a year" figure was computed
     * inline in an admin Blade template and stored nowhere, so nothing could act
     * on it.
     */
    public function up(): void
    {
        Schema::table('vip_plans', function (Blueprint $table) {
            // How long one paid cycle lasts. 12 keeps the previous hardcoded year.
            $table->unsignedInteger('validity_months')->default(12)->after('renewal_price');
        });

        Schema::table('vip_microsites', function (Blueprint $table) {
            $table->timestamp('plan_expires_at')->nullable()->after('activated_at');
            $table->index('plan_expires_at');
        });

        // Backfill live microsites off their own plan's validity so no one who is
        // already activated silently falls into "expired" on deploy.
        DB::table('vip_microsites')
            ->join('vip_plans', 'vip_plans.id', '=', 'vip_microsites.vip_plan_id')
            ->whereNotNull('vip_microsites.activated_at')
            ->update([
                'vip_microsites.plan_expires_at' => DB::raw(
                    'DATE_ADD(vip_microsites.activated_at, INTERVAL vip_plans.validity_months MONTH)'
                ),
            ]);
    }

    public function down(): void
    {
        Schema::table('vip_microsites', function (Blueprint $table) {
            $table->dropIndex(['plan_expires_at']);
            $table->dropColumn('plan_expires_at');
        });

        Schema::table('vip_plans', function (Blueprint $table) {
            $table->dropColumn('validity_months');
        });
    }
};
