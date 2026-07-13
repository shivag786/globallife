<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Sunita Mehta',
                'city' => 'Jaipur',
                'role_title' => 'Customer',
                'rating' => 5,
                'content' => "Family Prowell Nutrition has genuinely helped my children's health. It's now part of our daily routine.",
                'display_order' => 1,
            ],
            [
                'name' => 'Rahul Kumar',
                'city' => 'Delhi NCR',
                'role_title' => 'Customer',
                'rating' => 5,
                'content' => 'The Jasmine Bloom fragrance lasts the entire workday — exactly what I needed.',
                'display_order' => 2,
            ],
            [
                'name' => 'Priya Verma',
                'city' => 'Indore',
                'role_title' => 'Distributor',
                'rating' => 5,
                'content' => "What stood out to me was the honesty and support from the company — it's a business built on trust.",
                'display_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::firstOrCreate(
                ['name' => $testimonial['name'], 'city' => $testimonial['city']],
                $testimonial
            );
        }
    }
}
