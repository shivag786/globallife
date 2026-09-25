@php
    $microsite = $user->vipMicrosite;
    // $stats is null when the member has no microsite yet; the tiles below are
    // only rendered inside @if ($microsite), so don't build them at all.
    $tiles = $stats === null ? [] : [
        ['label' => 'Total Visitors', 'value' => $stats['total_visitors'], 'icon' => 'eye', 'color' => 'sky'],
        ["label" => "Today's Visitors", 'value' => $stats['today_visitors'], 'icon' => 'sparkles', 'color' => 'indigo'],
        ['label' => 'Total Leads', 'value' => $stats['total_leads'], 'icon' => 'inbox', 'color' => 'emerald', 'href' => route('vip.leads.index'), 'cta' => 'View leads'],
        ['label' => 'WhatsApp Clicks', 'value' => $stats['whatsapp_clicks'], 'icon' => 'chat-bubble', 'color' => 'teal'],
        ['label' => 'Call Clicks', 'value' => $stats['call_clicks'], 'icon' => 'phone', 'color' => 'violet'],
        ['label' => 'Direction Clicks', 'value' => $stats['direction_clicks'], 'icon' => 'map-pin', 'color' => 'amber'],
        ['label' => 'Website Clicks', 'value' => $stats['website_clicks'], 'icon' => 'share', 'color' => 'cyan'],
        ['label' => 'Booking Requests', 'value' => $stats['booking_requests'], 'icon' => 'calendar', 'color' => 'rose'],
        ['label' => 'Reviews', 'value' => $stats['review_count'], 'icon' => 'star', 'color' => 'brand', 'href' => route('vip.reviews.index'), 'cta' => 'Moderate'],
    ];
@endphp
<x-layouts.app title="VIP Dashboard" heading="VIP Dashboard">
    {{-- Warn before the page goes dark, so they can pay their partner in time. --}}
    @if ($microsite?->isExpiringSoon())
        @php $daysLeft = $microsite->daysUntilExpiry(); @endphp
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4">
            <p class="font-semibold text-amber-800">
                Your plan expires on {{ $microsite->plan_expires_at->format('d M Y') }}
                &mdash; {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left
            </p>
            <p class="text-sm text-amber-700 mt-1">
                Contact your Commission Partner to renew. Renewing now adds the new term on top of the days
                you have left, so nothing is wasted &mdash; and your page never goes offline.
            </p>
        </div>
    @endif

    {{-- Without this the member has no way to know their public page went dark. --}}
    @if ($microsite?->hasExpiredPlan())
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4">
            <p class="font-semibold text-amber-800">Your plan expired on {{ $microsite->plan_expires_at->format('d M Y') }}</p>
            <p class="text-sm text-amber-700 mt-1">
                Visitors to your page are currently seeing a maintenance notice. Contact your Commission
                Partner to renew — your page goes live again as soon as they approve it. Your content,
                leads and reviews are all safe in the meantime.
            </p>
        </div>
    @endif

    {{-- Not activated yet: the page is not public, and only their Commission
         Partner can change that. --}}
    @if ($microsite && ! $microsite->isActivated())
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4">
            <p class="font-semibold text-amber-800">Your page is not live yet</p>
            <p class="text-sm text-amber-700 mt-1">
                Visitors currently see a maintenance notice. Your Commission Partner activates it once your
                plan is paid &mdash; everything you add here is saved in the meantime and goes live with it.
            </p>
        </div>
    @endif

    @unless ($microsite)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4">
            <p class="font-semibold text-amber-800">Your business page has not been set up yet</p>
            <p class="text-sm text-amber-700 mt-1">
                Your Commission Partner creates it for you. Visitor and enquiry figures appear here once
                it is live &mdash; your earnings and withdrawals below work regardless.
            </p>
        </div>
    @endunless

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

    {{-- Earnings first: it is the one part of this page that works whether or not
         the business page exists, and it is what members come here for. --}}
    <div class="mb-3 flex items-center gap-2">
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
            <x-icon name="rupee" class="w-4 h-4" />
        </span>
        <h2 class="font-semibold text-slate-800">Earnings</h2>
        <span class="text-xs text-slate-400">&mdash; your commission wallet</span>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <x-stat-tile label="Withdrawable Balance" :value="$earnings['balance']" money icon="rupee"
            color="emerald" :href="route('wallet.index')" cta="Wallet & Withdrawals"
            hint="Ready to request" />
        <x-stat-tile label="Pending Commission" :value="$earnings['pending']" money icon="sparkles"
            color="amber" :href="route('wallet.index')" cta="See breakdown"
            hint="Unlocks as orders deliver" />
        <x-stat-tile label="Lifetime Earnings" :value="$earnings['lifetime']" money icon="star"
            color="brand" :href="route('wallet.index')" cta="View history" />

        @if ($earnings['open_request'])
            <x-stat-tile label="Withdrawal Requested" :value="$earnings['open_request']->amount" money
                icon="truck" color="violet" :href="route('wallet.index')" cta="Track request"
                :hint="'Awaiting payment since '.$earnings['open_request']->created_at->format('d M')" />
        @else
            <x-stat-tile label="Withdrawal Requests" value="0" icon="inbox" color="sky"
                :href="route('wallet.index')" cta="Request a withdrawal" hint="None open right now" />
        @endif
    </div>

    @if ($microsite)
        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                <x-icon name="eye" class="w-4 h-4" />
            </span>
            <h2 class="font-semibold text-slate-800">Business Page</h2>
            <span class="text-xs text-slate-400">&mdash; visitors &amp; engagement</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            @foreach ($tiles as $tile)
                <x-stat-tile :label="$tile['label']" :value="$tile['value']" :icon="$tile['icon']"
                    :color="$tile['color']" :href="$tile['href'] ?? null" :cta="$tile['cta'] ?? null" />
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
