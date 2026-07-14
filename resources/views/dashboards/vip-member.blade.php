@php
    $microsite = $user->vipMicrosite;
    $tiles = [
        ['label' => 'Total Visitors', 'value' => $stats['total_visitors'], 'icon' => 'eye'],
        ["label" => "Today's Visitors", 'value' => $stats['today_visitors'], 'icon' => 'sparkles'],
        ['label' => 'Total Leads', 'value' => $stats['total_leads'], 'icon' => 'inbox'],
        ['label' => 'WhatsApp Clicks', 'value' => $stats['whatsapp_clicks'], 'icon' => 'chat-bubble'],
        ['label' => 'Call Clicks', 'value' => $stats['call_clicks'], 'icon' => 'phone'],
        ['label' => 'Direction Clicks', 'value' => $stats['direction_clicks'], 'icon' => 'map-pin'],
        ['label' => 'Website Clicks', 'value' => $stats['website_clicks'], 'icon' => 'share'],
        ['label' => 'Booking Requests', 'value' => $stats['booking_requests'], 'icon' => 'calendar'],
        ['label' => 'Reviews', 'value' => $stats['review_count'], 'icon' => 'star'],
    ];
@endphp
<x-layouts.app title="VIP Dashboard" heading="VIP Dashboard">
    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="text-slate-600">Welcome back, <strong>{{ $user->name }}</strong>.</p>
            @if ($microsite)
                <p class="text-sm text-slate-400">{{ $microsite->business_name }} &middot; {{ $microsite->city->name }} &middot; {{ $microsite->vipPlan->name }}</p>
            @endif
        </div>
        @if ($microsite)
            <a href="{{ url($microsite->publicPath()) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-brand-700 text-white text-sm px-4 py-2.5 rounded-full font-medium hover:bg-brand-800 premium-shadow transition">
                View Your Page <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        @endif
    </div>

    @if ($microsite)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            @foreach ($tiles as $tile)
                <div class="bg-white rounded-2xl p-5 border border-slate-100 premium-shadow">
                    <div class="w-9 h-9 rounded-full bg-brand-700/10 flex items-center justify-center mb-3">
                        <x-icon name="{{ $tile['icon'] }}" class="w-5 h-5 text-brand-700" />
                    </div>
                    <p class="text-2xl font-bold text-brand-900" data-countup="{{ $tile['value'] }}">0</p>
                    <p class="text-xs uppercase tracking-wide text-slate-400 mt-1">{{ $tile['label'] }}</p>
                </div>
            @endforeach

            <div class="bg-gradient-to-br from-brand-900 to-brand-950 rounded-2xl p-5 text-white premium-shadow">
                <p class="text-xs uppercase tracking-wide text-brand-200 mb-2">Profile Completion</p>
                <p class="text-3xl font-extrabold text-gold-400">{{ $stats['completion'] }}%</p>
                <div class="w-full h-2 bg-white/10 rounded-full mt-3 overflow-hidden">
                    <div class="h-full bg-gold-400 rounded-full" style="width: {{ $stats['completion'] }}%"></div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-4 mb-8">
            <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100 premium-shadow">
                <h2 class="font-semibold text-brand-900 mb-1">Visitors</h2>
                <p class="text-xs text-slate-400 mb-3">Page views over the last 14 days</p>
                <x-apex-chart id="chart-vip-visitors" type="area" :height="290"
                    :series="[['name' => 'Visitors', 'data' => $visitorsChart['data']]]"
                    :categories="$visitorsChart['categories']" :colors="['#2c704c']" />
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 premium-shadow">
                <h2 class="font-semibold text-brand-900 mb-1">Engagement</h2>
                <p class="text-xs text-slate-400 mb-3">Contact button clicks</p>
                <x-apex-chart id="chart-vip-clicks" type="donut" :height="290"
                    :series="[$stats['whatsapp_clicks'], $stats['call_clicks'], $stats['direction_clicks'], $stats['website_clicks']]"
                    :labels="['WhatsApp', 'Call', 'Directions', 'Website']"
                    :colors="['#25d366', '#2c704c', '#d4af37', '#5fa97e']" />
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4 mb-8">
            <a href="{{ route('vip.profile.edit') }}" class="bg-white rounded-xl p-4 border border-slate-100 hover:border-brand-300 transition premium-shadow">
                <p class="font-semibold text-brand-900 text-sm">Edit Business Profile</p>
                <p class="text-xs text-slate-400 mt-1">Basic info, contact, hours, social</p>
            </a>
            <a href="{{ route('vip.modules.edit') }}" class="bg-white rounded-xl p-4 border border-slate-100 hover:border-brand-300 transition premium-shadow">
                <p class="font-semibold text-brand-900 text-sm">Manage Section Visibility</p>
                <p class="text-xs text-slate-400 mt-1">Turn page sections on/off</p>
            </a>
            <a href="{{ route('vip.leads.index') }}" class="bg-white rounded-xl p-4 border border-slate-100 hover:border-brand-300 transition premium-shadow">
                <p class="font-semibold text-brand-900 text-sm">View Leads</p>
                <p class="text-xs text-slate-400 mt-1">{{ $stats['total_leads'] }} total enquiries</p>
            </a>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100 text-slate-500">
        Bookings, Offers/Coupons, Team Members, Certifications, and a Download Center ship in Phase 2.
    </div>
</x-layouts.app>
