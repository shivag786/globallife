<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'title' => 'National Distributor Training — Delhi NCR',
                'description' => "A full-day training session covering product knowledge, onboarding best practices, and Q&A with senior distributors.\n\nOpen to all active distributors and prospective members.",
                'event_date' => now()->addDays(14)->setTime(10, 0),
                'location' => 'Delhi NCR',
                'display_order' => 1,
            ],
            [
                'title' => 'Online Wellness Product Masterclass',
                'description' => "Live online session walking through the full wellness product range, ideal for new customers and distributors.",
                'event_date' => now()->addDays(7)->setTime(18, 0),
                'location' => 'Online (Zoom)',
                'display_order' => 2,
            ],
            [
                'title' => 'Jhansi Regional Meetup',
                'description' => "A community meetup for Jhansi-area distributors and customers — product demos, success stories, and networking.",
                'event_date' => now()->addDays(21)->setTime(11, 0),
                'location' => 'Jhansi',
                'display_order' => 3,
            ],
        ];

        foreach ($events as $event) {
            Event::firstOrCreate(
                ['title' => $event['title']],
                [...$event, 'slug' => Str::slug($event['title']).'-'.Str::random(6), 'status' => 'active']
            );
        }
    }
}
