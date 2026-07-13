<?php

namespace Database\Seeders;

use App\Models\VipPlan;
use Illuminate\Database\Seeder;

class VipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Silver VIP', 'slug' => 'silver-vip', 'display_order' => 1, 'upgrade_priority' => 1,
                'monthly_price' => 499, 'yearly_price' => 4999, 'joining_price' => 999, 'renewal_price' => 4999,
                'microsite_limit' => 1, 'landing_page_limit' => 1, 'blog_limit' => 5, 'analytics_limit_days' => 30, 'storage_limit_mb' => 250,
                'features' => ['1 Microsite', '1 Landing Page', '5 Blogs', '30-day Analytics'],
                'highlight_features' => ['1 Microsite'],
            ],
            [
                'name' => 'Gold VIP', 'slug' => 'gold-vip', 'display_order' => 2, 'upgrade_priority' => 2,
                'monthly_price' => 999, 'yearly_price' => 9999, 'joining_price' => 1499, 'renewal_price' => 9999,
                'microsite_limit' => 2, 'landing_page_limit' => 3, 'blog_limit' => 20, 'analytics_limit_days' => 90, 'storage_limit_mb' => 1024,
                'features' => ['2 Microsites', '3 Landing Pages', '20 Blogs', '90-day Analytics'],
                'highlight_features' => ['2 Microsites', '90-day Analytics'],
            ],
            [
                'name' => 'Platinum VIP', 'slug' => 'platinum-vip', 'display_order' => 3, 'upgrade_priority' => 3,
                'monthly_price' => 1999, 'yearly_price' => 19999, 'joining_price' => 1999, 'renewal_price' => 19999,
                'microsite_limit' => 5, 'landing_page_limit' => 10, 'blog_limit' => 100, 'analytics_limit_days' => 180, 'storage_limit_mb' => 5120,
                'features' => ['5 Microsites', '10 Landing Pages', '100 Blogs', '180-day Analytics', 'Affiliate Products'],
                'highlight_features' => ['5 Microsites', 'Affiliate Products'],
            ],
            [
                'name' => 'Diamond VIP', 'slug' => 'diamond-vip', 'display_order' => 4, 'upgrade_priority' => 4,
                'monthly_price' => 4999, 'yearly_price' => 49999, 'joining_price' => 2999, 'renewal_price' => 49999,
                'microsite_limit' => 999, 'landing_page_limit' => 999, 'blog_limit' => 999, 'analytics_limit_days' => 365, 'storage_limit_mb' => 20480,
                'features' => ['Unlimited Microsites', 'Unlimited Landing Pages', 'Unlimited Blogs', '365-day Analytics', 'Affiliate Products', 'Priority Support'],
                'highlight_features' => ['Unlimited Everything', 'Priority Support'],
            ],
        ];

        foreach ($plans as $plan) {
            VipPlan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
