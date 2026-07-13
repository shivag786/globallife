<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            'contact_email' => 'cmd@globallife.in',
            'contact_whatsapp' => '+91 90000 00000',
            'contact_address' => 'Patel Nagar, Gurgaon, Haryana, India',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_youtube' => '',
            'social_linkedin' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
