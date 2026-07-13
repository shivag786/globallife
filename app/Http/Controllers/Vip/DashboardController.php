<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user()->load('vipMicrosite.city', 'vipMicrosite.vipPlan');
        $microsite = $user->vipMicrosite;
        $events = $microsite->events();

        $stats = [
            'total_visitors' => (clone $events)->where('event_type', 'page_view')->count(),
            'today_visitors' => (clone $events)->where('event_type', 'page_view')->whereDate('created_at', today())->count(),
            'total_leads' => Lead::where('vip_microsite_id', $microsite->id)->count(),
            'whatsapp_clicks' => (clone $events)->where('event_type', 'whatsapp_click')->count(),
            'call_clicks' => (clone $events)->where('event_type', 'call_click')->count(),
            'direction_clicks' => (clone $events)->where('event_type', 'direction_click')->count(),
            'website_clicks' => (clone $events)->where('event_type', 'website_click')->count(),
            'booking_requests' => (clone $events)->where('event_type', 'booking_click')->count(),
            'review_count' => $microsite->reviews()->where('status', 'approved')->count(),
            'completion' => $microsite->completionPercentage(),
        ];

        return view('dashboards.vip-member', ['user' => $user, 'stats' => $stats]);
    }
}
