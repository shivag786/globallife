<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Jasmine Bloom EDP',
                'slug' => 'jasmine-bloom-edp',
                'category' => 'Fragrance',
                'tags' => ['perfume', 'organic', 'long-lasting'],
                'badge' => 'New Launch',
                'short_description' => 'Long-lasting, refreshing & soothing organic eau de parfum.',
                'long_description' => "<p>Jasmine Bloom EDP is crafted from organically sourced jasmine absolute, blended for an all-day fresh and calming scent.</p><p>Free from harsh synthetics, it's gentle enough for daily wear.</p>",
                'specs' => ['Type' => 'Organic EDP', 'Size' => '100ml', 'Longevity' => '8+ hours'],
                'is_featured' => true,
                'display_order' => 1,
                'meta_title' => 'Jasmine Bloom EDP — Organic Perfume | Global Life',
                'meta_description' => 'Long-lasting organic jasmine eau de parfum, 100ml.',
            ],
            [
                'name' => 'Lavender Calm EDP',
                'slug' => 'lavender-calm-edp',
                'category' => 'Fragrance',
                'tags' => ['perfume', 'calming'],
                'short_description' => 'Calming & stress-relieving lavender fragrance that stays fresh all day.',
                'long_description' => '<p>A soothing lavender blend designed to help you stay calm and confident through a long day.</p>',
                'specs' => ['Type' => 'Eau de Parfum', 'Size' => '100ml'],
                'is_featured' => true,
                'display_order' => 2,
                'meta_title' => 'Lavender Calm EDP | Global Life',
                'meta_description' => 'A calming, stress-relieving lavender eau de parfum.',
            ],
            [
                'name' => 'Family Prowell Nutrition',
                'slug' => 'family-prowell-nutrition',
                'category' => 'Wellness',
                'tags' => ['nutrition', 'protein', 'family'],
                'badge' => 'Bestseller',
                'short_description' => 'Multi-source protein blend that boosts strength & immunity for the whole family.',
                'long_description' => '<p>Family Prowell Nutrition combines multiple plant and dairy protein sources into one easy daily supplement.</p><p>Suitable for all age groups, from growing children to active adults.</p>',
                'specs' => ['Form' => 'Powder', 'Serving Size' => '30g', 'Suitable For' => 'All ages'],
                'is_featured' => true,
                'display_order' => 3,
                'meta_title' => 'Family Prowell Nutrition — Multi-Source Protein | Global Life',
                'meta_description' => 'Multi-source protein nutrition supplement for the whole family.',
            ],
            [
                'name' => 'Vitality Herbal Formula',
                'slug' => 'vitality-herbal-formula',
                'category' => 'Herbal Wellness',
                'tags' => ['herbal', 'stamina', 'wellness'],
                'short_description' => 'Boosts stamina & confidence with a natural herbal formula.',
                'long_description' => '<p>A traditional herbal blend formulated to support everyday energy and vitality, made from natural ingredients.</p>',
                'specs' => ['Form' => 'Capsules', 'Pack Size' => '60 capsules'],
                'is_featured' => true,
                'display_order' => 4,
                'meta_title' => 'Vitality Herbal Formula | Global Life',
                'meta_description' => 'Natural herbal formula to boost stamina and everyday vitality.',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['slug' => $product['slug']], $product);
        }
    }
}
