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

            // Payment gateway defaults — Razorpay off until admin adds keys at
            // /admin/settings/payment; COD keeps checkout working meanwhile.
            'razorpay_enabled' => '0',
            'razorpay_mode' => 'test',
            'razorpay_currency' => 'INR',
            'cod_enabled' => '1',
            'payment_test_mode' => '0',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
