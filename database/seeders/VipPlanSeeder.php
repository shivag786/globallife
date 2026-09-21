<?php

namespace Database\Seeders;

use App\Models\VipPlan;
use Illuminate\Database\Seeder;

class VipPlanSeeder extends Seeder
{
    /**
     * The four packages a member joins on and renews onto. Prices, validity and
     * the product/service caps are also written by the
     * 2026_09_21_000003 migration, so seeding an existing install is a no-op —
     * this keeps a freshly seeded database in step with it.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Growth', 'slug' => 'growth', 'display_order' => 1, 'upgrade_priority' => 1,
                'price' => 4999, 'validity_months' => 3, 'product_limit' => 15, 'service_limit' => 6,
                'highlight_features' => [],
            ],
            [
                'name' => 'Professional', 'slug' => 'professional', 'display_order' => 2, 'upgrade_priority' => 2,
                'price' => 14999, 'validity_months' => 6, 'product_limit' => 35, 'service_limit' => 15,
                'highlight_features' => ['Most Popular'],
            ],
            [
                'name' => 'Growth Plus', 'slug' => 'growth-plus', 'display_order' => 3, 'upgrade_priority' => 3,
                'price' => 24999, 'validity_months' => 12, 'product_limit' => 50, 'service_limit' => 30,
                'highlight_features' => [],
            ],
            [
                'name' => 'Premium', 'slug' => 'premium', 'display_order' => 4, 'upgrade_priority' => 4,
                'price' => 41999, 'validity_months' => 12, 'product_limit' => 100, 'service_limit' => 100,
                'highlight_features' => [],
            ],
        ];

        foreach ($plans as $plan) {
            $price = $plan['price'];
            $months = $plan['validity_months'];
            unset($plan['price']);

            VipPlan::updateOrCreate(['slug' => $plan['slug']], $plan + [
                'monthly_price' => 0,
                'yearly_price' => $price,
                'joining_price' => $price,
                'renewal_price' => $price,
                'status' => 'active',
                'features' => [
                    $months === 12 ? '1 Year Validity' : $months.' Months Validity',
                    $plan['product_limit'].' Product Catalogue',
                    $plan['service_limit'].' Services',
                ],
            ]);
        }
    }
}
