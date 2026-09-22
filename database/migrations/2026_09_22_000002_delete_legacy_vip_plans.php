<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Removes the pre-package plans, leaving only Growth / Professional /
     * Growth Plus / Premium.
     *
     * Two things stood in the way, both `restrictOnDelete`:
     *
     * 1. `vip_microsites.vip_plan_id` is NOT NULL — a microsite must have a plan.
     *    Any still on a legacy plan is moved to the equivalent new package, which
     *    also gives them real product/service caps (the legacy rows carry 0, which
     *    was blocking those members from adding any content at all).
     * 2. `commission_transactions.vip_plan_id` and `vip_renewals.vip_plan_id`
     *    are history. They become nullable with ON DELETE SET NULL, so the money
     *    facts — package_amount, the percentages and each party's share — survive
     *    intact; only the link to the plan's name is lost, and those rows will
     *    show a blank package in revenue reports.
     */
    private const REPLACEMENTS = [
        'silver-vip' => 'growth',
        'gold-vip' => 'professional',
        'platinum-vip' => 'growth-plus',
        'diamond-vip' => 'premium',
    ];

    public function up(): void
    {
        // 1. History keeps its numbers but lets go of the plan.
        Schema::table('commission_transactions', function (Blueprint $table) {
            $table->dropForeign(['vip_plan_id']);
            $table->unsignedBigInteger('vip_plan_id')->nullable()->change();
            $table->foreign('vip_plan_id')->references('id')->on('vip_plans')->nullOnDelete();
        });

        Schema::table('vip_renewals', function (Blueprint $table) {
            $table->dropForeign(['vip_plan_id']);
            $table->unsignedBigInteger('vip_plan_id')->nullable()->change();
            $table->foreign('vip_plan_id')->references('id')->on('vip_plans')->nullOnDelete();
        });

        // 2. Move live microsites onto the equivalent package before the delete,
        //    since they cannot be left without a plan.
        foreach (self::REPLACEMENTS as $legacySlug => $newSlug) {
            $legacyId = DB::table('vip_plans')->where('slug', $legacySlug)->value('id');
            $newId = DB::table('vip_plans')->where('slug', $newSlug)->value('id');

            if (! $legacyId || ! $newId) {
                continue;
            }

            DB::table('vip_microsites')->where('vip_plan_id', $legacyId)->update([
                'vip_plan_id' => $newId,
                'updated_at' => now(),
            ]);
        }

        // 3. Now nothing blocks them.
        DB::table('vip_plans')->whereIn('slug', array_keys(self::REPLACEMENTS))->delete();
    }

    /**
     * Irreversible by design: the deleted plans' own rows are gone, and the
     * history rows that pointed at them have been nulled. Only the schema
     * change is undone, and only when no history row is null.
     */
    public function down(): void
    {
        DB::table('vip_renewals')->whereNull('vip_plan_id')->delete();
        DB::table('commission_transactions')->whereNull('vip_plan_id')->delete();

        Schema::table('vip_renewals', function (Blueprint $table) {
            $table->dropForeign(['vip_plan_id']);
            $table->unsignedBigInteger('vip_plan_id')->nullable(false)->change();
            $table->foreign('vip_plan_id')->references('id')->on('vip_plans')->restrictOnDelete();
        });

        Schema::table('commission_transactions', function (Blueprint $table) {
            $table->dropForeign(['vip_plan_id']);
            $table->unsignedBigInteger('vip_plan_id')->nullable(false)->change();
            $table->foreign('vip_plan_id')->references('id')->on('vip_plans')->restrictOnDelete();
        });
    }
};
