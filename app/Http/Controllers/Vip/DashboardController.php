<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfileEvent;
use App\Models\CommissionEarning;
use App\Models\Lead;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Support\ChartData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user()->load('vipMicrosite.city', 'vipMicrosite.vipPlan', 'wallet');
        $microsite = $user->vipMicrosite;

        // Earnings do not depend on the business page, so they are shown either
        // way. Without this, a member whose page is not set up yet got a
        // dashboard with nothing on it at all.
        $earnings = $this->earnings($user);

        // A vip_member without a microsite used to crash below on a null
        // dereference, which took the whole panel down: with the dashboard
        // 500ing they could not reach the sidebar, so Wallet and everything else
        // became unreachable. Every microsite-derived stat is simply absent.
        if (! $microsite) {
            return view('dashboards.vip-member', [
                'user' => $user,
                'stats' => null,
                'visitorsChart' => null,
                'earnings' => $earnings,
            ]);
        }

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

        $visitorsChart = ChartData::daily(
            BusinessProfileEvent::where('vip_microsite_id', $microsite->id)->where('event_type', 'page_view'),
            'created_at',
            'COUNT(*)',
            14
        );

        return view('dashboards.vip-member', [
            'user' => $user,
            'stats' => $stats,
            'visitorsChart' => $visitorsChart,
            'earnings' => $earnings,
        ]);
    }

    /**
     * Wallet position for a VIP member: what they can withdraw, what is still
     * pending delivery, and whether a withdrawal request is already open.
     *
     * @return array<string, mixed>
     */
    private function earnings(User $user): array
    {
        return [
            'balance' => (float) ($user->wallet?->balance ?? 0),
            'pending' => (float) CommissionEarning::where('beneficiary_id', $user->id)->pending()->sum('amount'),
            'lifetime' => (float) CommissionEarning::where('beneficiary_id', $user->id)->approved()->sum('amount'),
            'open_request' => WithdrawalRequest::where('user_id', $user->id)->pending()->latest('created_at')->first(),
        ];
    }
}
