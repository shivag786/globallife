<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Full takeover of homepage content — replace any previously seeded/edited sections with the curated set.
        HomeSection::query()->delete();

        $sections = [
            [
                'type' => 'hero',
                'title' => 'Wellness & fragrance crafted for every Indian family',
                'subtitle' => 'Herbal supplements and luxury perfumes, backed by transparent pricing and a nationwide distributor network.',
                'cta_label' => 'Explore Products',
                'cta_url' => '/products',
                'items' => [
                    ['title' => 'FSSAI Certified'],
                    ['title' => 'Lab Tested'],
                    ['title' => 'Pan-India Delivery'],
                    ['title' => 'Transparent Pricing'],
                ],
                'display_order' => 1,
            ],
            [
                'type' => 'founder_quote',
                'title' => 'Founder, Global Life',
                'subtitle' => 'Our Promise',
                'content' => "<p>If the product doesn't genuinely help people, we won't sell it. That's the one rule we've never broken.</p>",
                'display_order' => 2,
            ],
            [
                'type' => 'about',
                'title' => 'A homegrown brand built on transparency, quality, and people',
                'content' => '<p>Global Life connects city managers, VIP members, and customers on a single platform.</p><p>From wellness supplements to fine fragrances, every product we sell is verified before it reaches you — and every city has its own dedicated managers driving local growth.</p>',
                'display_order' => 3,
            ],
            [
                'type' => 'features',
                'title' => 'Why Global Life',
                'items' => [
                    ['title' => 'Verified Quality', 'description' => 'Every product is lab tested and FSSAI certified before it ships.'],
                    ['title' => 'People-First Culture', 'description' => 'Customers and distributors are treated as partners, not numbers.'],
                    ['title' => 'Transparent Pricing', 'description' => 'No hidden costs — every discount and commission is logged.'],
                    ['title' => 'Pan-India Delivery', 'description' => 'A growing distribution network across major Indian cities.'],
                ],
                'display_order' => 4,
            ],
            [
                'type' => 'products_showcase',
                'title' => 'Four products. One promise of quality',
                'subtitle' => 'Our featured range',
                'display_order' => 5,
            ],
            [
                'type' => 'stats',
                'title' => 'Growing Every Day',
                'items' => [
                    ['label' => 'Years of Trust', 'value' => '10+'],
                    ['label' => 'Active Distributors', 'value' => '750+'],
                    ['label' => 'Happy Customers', 'value' => '2.5L+'],
                    ['label' => 'Cities Served', 'value' => '19+'],
                    ['label' => 'Total Payouts Disbursed', 'value' => '₹1.2 Cr+'],
                    ['label' => 'Uptime', 'value' => '99.9%'],
                ],
                'display_order' => 6,
            ],
            [
                'type' => 'team',
                'title' => 'The People Behind Global Life',
                'subtitle' => 'A leadership team focused on products, people, and long-term trust',
                'items' => [
                    ['name' => 'Founder Name', 'role' => 'Founder & CEO'],
                    ['name' => 'Co-Founder Name', 'role' => 'Head of Operations'],
                    ['name' => 'Team Member Name', 'role' => 'National Sales Director'],
                    ['name' => 'Team Member Name', 'role' => 'Head of Distributor Success'],
                ],
                'display_order' => 7,
            ],
            [
                'type' => 'blog_showcase',
                'title' => 'From the Blog',
                'subtitle' => 'Wellness tips, product guides, and company updates',
                'display_order' => 8,
            ],
            [
                'type' => 'testimonials_showcase',
                'title' => 'Trusted by families across India',
                'subtitle' => 'Real people, real results',
                'display_order' => 9,
            ],
            [
                'type' => 'presence_map',
                'title' => 'From metro hubs to growing towns',
                'subtitle' => 'Our pan-India distribution network',
                'display_order' => 10,
            ],
            [
                'type' => 'upcoming_events',
                'title' => 'Upcoming Training Events',
                'subtitle' => 'Join us online or in person',
                'display_order' => 11,
            ],
            [
                'type' => 'business_opportunity',
                'title' => 'Build a business around products people actually want',
                'content' => "<p>Most opportunities ask you to sell first and believe later. We do it the other way around.</p><p>Every Global Life distributor starts out as a customer — using the products, seeing the results, and only then choosing to share them. That's why the people who join us tend to stay: they're not selling a pitch, they're sharing something they already trust.</p><p>Whether you're looking for a side income or building toward something full-time, the path is the same — start small, grow at your own pace, and lean on a community that's been exactly where you are.</p>",
                'cta_label' => 'Explore the Opportunity',
                'cta_url' => '/vip-plans',
                'items' => [
                    ['title' => 'Step-by-Step Onboarding', 'description' => 'A structured, guided start — no guesswork, no overwhelm, just a clear first 30 days.'],
                    ['title' => 'Marketing Materials', 'description' => 'Ready-to-use digital creatives, product copy, and WhatsApp-ready content for your network.'],
                    ['title' => 'National Training Events', 'description' => 'Regular online and in-person sessions to sharpen your product knowledge and selling skills.'],
                    ['title' => 'Mentorship-Driven Community', 'description' => 'Direct support from city managers and senior distributors who\'ve built their own teams.'],
                ],
                'display_order' => 12,
            ],
            [
                'type' => 'process_steps',
                'title' => 'How Partnership Works',
                'items' => [
                    ['title' => 'Become a customer first', 'description' => 'Experience the products before you ever talk business.'],
                    ['title' => 'Register as a distributor', 'description' => 'Simple onboarding once you\'re ready to share.'],
                    ['title' => 'Share with your network', 'description' => 'Use the tools and training to introduce others.'],
                    ['title' => 'Grow with mentorship', 'description' => 'Build your own team with ongoing support.'],
                ],
                'display_order' => 13,
            ],
            [
                'type' => 'vip_plans',
                'title' => 'Choose Your VIP Plan',
                'subtitle' => 'Flexible membership tiers for every stage of your business',
                'display_order' => 14,
            ],
            [
                'type' => 'enquiry_form',
                'title' => 'Have a Question?',
                'subtitle' => 'Send us your details and a city manager will get back to you.',
                'display_order' => 15,
            ],
            [
                'type' => 'cta',
                'title' => 'Ready to Get Started?',
                'subtitle' => 'Talk to us today. No pressure, just answers.',
                'cta_label' => 'View VIP Plans',
                'cta_url' => '/vip-plans',
                'display_order' => 16,
            ],
        ];

        foreach ($sections as $section) {
            HomeSection::create($section);
        }
    }
}
