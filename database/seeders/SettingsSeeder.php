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
            'contact_email' => 'info@globallife.co.in',
            'contact_email_hr' => 'hr@globallife.co.in',
            // Keep the +91: the wa.me link is built from these digits and needs the country code.
            'contact_whatsapp' => '+91 88699 11138',
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
