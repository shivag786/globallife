<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The four real packages a Commission Partner picks from when renewing a VIP
     * member, with the content caps each one buys.
     *
     * `product_limit` caps the VIP's own products (`business_products`, the
     * /vip/products page) and `service_limit` caps `business_services`. Both are
     * enforced as a cap on the TOTAL row count, so existing items always count
     * against the new plan — a member with 15 products who renews on GROWTH (15)
     * can keep and edit those 15 but cannot add a 16th, while PROFESSIONAL (35)
     * leaves them 20 more to add.
     */
    private const PACKAGES = [
        [
            'name' => 'Growth', 'slug' => 'growth', 'price' => 4999, 'validity_months' => 3,
            'product_limit' => 15, 'service_limit' => 6, 'order' => 1,
            'highlight' => [],
        ],
        [
            'name' => 'Professional', 'slug' => 'professional', 'price' => 14999, 'validity_months' => 6,
            'product_limit' => 35, 'service_limit' => 15, 'order' => 2,
            'highlight' => ['Most Popular'],
        ],
        [
            'name' => 'Growth Plus', 'slug' => 'growth-plus', 'price' => 24999, 'validity_months' => 12,
            'product_limit' => 50, 'service_limit' => 30, 'order' => 3,
            'highlight' => [],
        ],
        [
            'name' => 'Premium', 'slug' => 'premium', 'price' => 41999, 'validity_months' => 12,
            'product_limit' => 100, 'service_limit' => 100, 'order' => 4,
            'highlight' => [],
        ],
    ];

    /** Plans the packages above replace. Deactivated, never deleted: live */
    /** microsites still reference them and keep doing so until they renew. */
    private const RETIRED_SLUGS = ['silver-vip', 'gold-vip', 'platinum-vip', 'diamond-vip'];

    public function up(): void
    {
        Schema::table('vip_plans', function (Blueprint $table) {
            $table->unsignedInteger('product_limit')->default(0)->after('validity_months');
            $table->unsignedInteger('service_limit')->default(0)->after('product_limit');
        });

        $now = now();

        foreach (self::PACKAGES as $package) {
            $months = $package['validity_months'];

            DB::table('vip_plans')->updateOrInsert(
                ['slug' => $package['slug']],
                [
                    'name' => $package['name'],
                    // One price per package: it is what the member pays to join and
                    // to renew, so the activation commission split stays correct.
                    'monthly_price' => 0,
                    'yearly_price' => $package['price'],
                    'joining_price' => $package['price'],
                    'renewal_price' => $package['price'],
                    'validity_months' => $months,
                    'product_limit' => $package['product_limit'],
                    'service_limit' => $package['service_limit'],
                    'features' => json_encode([
                        $months === 12 ? '1 Year Validity' : $months.' Months Validity',
                        $package['product_limit'].' Product Catalogue',
                        $package['service_limit'].' Services',
                    ]),
                    'highlight_features' => json_encode($package['highlight']),
                    'status' => 'active',
                    'display_order' => $package['order'],
                    'upgrade_priority' => $package['order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        DB::table('vip_plans')
            ->whereIn('slug', self::RETIRED_SLUGS)
            ->update(['status' => 'inactive', 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('vip_plans')->whereIn('slug', array_column(self::PACKAGES, 'slug'))->delete();
        DB::table('vip_plans')->whereIn('slug', self::RETIRED_SLUGS)->update(['status' => 'active']);

        Schema::table('vip_plans', function (Blueprint $table) {
            $table->dropColumn(['product_limit', 'service_limit']);
        });
    }
};
