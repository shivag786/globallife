<?php

namespace App\Http\Controllers;

use App\Models\VipMicrosite;
use Illuminate\Http\RedirectResponse;

class MicrositeClickController extends Controller
{
    private const EVENT_MAP = [
        'whatsapp' => 'whatsapp_click',
        'call' => 'call_click',
        'direction' => 'direction_click',
        'website' => 'website_click',
        'booking' => 'booking_click',
    ];

    public function redirect(VipMicrosite $vipMicrosite, string $type): RedirectResponse
    {
        abort_unless(array_key_exists($type, self::EVENT_MAP), 404);

        $vipMicrosite->events()->create(['event_type' => self::EVENT_MAP[$type]]);

        return match ($type) {
            'whatsapp' => redirect()->away('https://wa.me/'.preg_replace('/\D/', '', (string) $vipMicrosite->whatsapp_number)),
            'call' => redirect()->away('tel:'.$vipMicrosite->phone_number),
            'direction' => redirect()->away($vipMicrosite->google_map_url ?: 'https://maps.google.com'),
            'website' => redirect()->away($vipMicrosite->website_url ?: url($vipMicrosite->publicPath())),
            'booking' => redirect()->to(url($vipMicrosite->publicPath()).'#enquiry'),
        };
    }
}
