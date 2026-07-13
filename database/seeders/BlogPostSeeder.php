<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::where('email', 'admin@globallife.in')->first();

        $posts = [
            [
                'title' => '5 Daily Wellness Habits Backed by Tradition',
                'slug' => '5-daily-wellness-habits-backed-by-tradition',
                'category' => 'Wellness',
                'tags' => ['wellness', 'habits', 'health'],
                'excerpt' => 'Simple, traditional daily habits that support long-term family wellness.',
                'content' => "<p>Wellness isn't built in a day — it's built through small, consistent habits.</p><p>In this post we walk through five daily habits, inspired by traditional Indian wellness practices, that are easy to start today and support long-term health for the whole family.</p>",
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'views' => 184,
                'meta_title' => '5 Daily Wellness Habits Backed by Tradition | Global Life Blog',
                'meta_description' => 'Five simple, traditional daily habits that support long-term family wellness.',
            ],
            [
                'title' => 'How to Choose the Right Fragrance for Every Season',
                'slug' => 'how-to-choose-the-right-fragrance-for-every-season',
                'category' => 'Fragrance',
                'tags' => ['fragrance', 'perfume', 'guide'],
                'excerpt' => 'A simple guide to picking fragrances that match the season and your mood.',
                'content' => "<p>Fragrance is personal, but the season can guide your choice.</p><p>Lighter florals like jasmine work beautifully in warmer months, while calming notes like lavender suit cooler, slower days. Here's how to choose what fits you.</p>",
                'status' => 'published',
                'published_at' => now()->subDays(4),
                'views' => 97,
                'meta_title' => 'Choosing the Right Fragrance for Every Season | Global Life Blog',
                'meta_description' => 'A simple guide to picking fragrances that match the season and your mood.',
            ],
            [
                'title' => 'Becoming a Global Life Distributor: What to Expect',
                'slug' => 'becoming-a-global-life-distributor-what-to-expect',
                'category' => 'Business Opportunity',
                'tags' => ['business', 'distributor', 'opportunity'],
                'excerpt' => 'A first-hand look at onboarding, training, and mentorship for new distributors.',
                'content' => "<p>Starting as a distributor begins with becoming a customer first — so you genuinely understand the products you'll share.</p><p>From there, onboarding includes step-by-step training, marketing materials, and a mentorship-driven community to help you grow.</p>",
                'status' => 'published',
                'published_at' => now()->subDay(),
                'views' => 52,
                'meta_title' => 'Becoming a Global Life Distributor | Global Life Blog',
                'meta_description' => 'What to expect when you become a Global Life distributor — onboarding, training, and mentorship.',
            ],
        ];

        foreach ($posts as $post) {
            $created = BlogPost::firstOrCreate(['slug' => $post['slug']], [...$post, 'author_id' => $author?->id]);

            if ($created->wasRecentlyCreated) {
                $created->likes()->createMany([
                    ['like_token' => 'seed-demo-1'],
                    ['like_token' => 'seed-demo-2'],
                ]);
                $created->comments()->create([
                    'name' => 'Anita Sharma',
                    'content' => 'Really helpful, thank you for sharing this!',
                ]);
            }
        }
    }
}
