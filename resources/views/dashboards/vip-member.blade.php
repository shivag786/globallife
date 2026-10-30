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
        <div class="dash-alert">
            <span class="dash-alert__icon"><x-icon name="calendar" class="w-5 h-5" /></span>
            <div>
                <p class="dash-alert__title">
                    Your plan expires on {{ $microsite->plan_expires_at->format('d M Y') }}
                    &mdash; {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left
                </p>
                <p class="dash-alert__text">
                    Contact your Commission Partner to renew. Renewing now adds the new term on top of the days
                    you have left, so nothing is wasted &mdash; and your page never goes offline.
                </p>
            </div>
        </div>
    @endif

    {{-- Without this the member has no way to know their public page went dark. --}}
    @if ($microsite?->hasExpiredPlan())
        <div class="dash-alert">
            <span class="dash-alert__icon"><x-icon name="calendar" class="w-5 h-5" /></span>
            <div>
                <p class="dash-alert__title">Your plan expired on {{ $microsite->plan_expires_at->format('d M Y') }}</p>
                <p class="dash-alert__text">
                    Visitors to your page are currently seeing a maintenance notice. Contact your Commission
                    Partner to renew — your page goes live again as soon as they approve it. Your content,
                    leads and reviews are all safe in the meantime.
                </p>
            </div>
        </div>
    @endif

    {{-- Not activated yet: the page is not public, and only their Commission
         Partner can change that. --}}
    @if ($microsite && ! $microsite->isActivated())
        <div class="dash-alert">
            <span class="dash-alert__icon"><x-icon name="eye-slash" class="w-5 h-5" /></span>
            <div>
                <p class="dash-alert__title">Your page is not live yet</p>
                <p class="dash-alert__text">
                    Visitors currently see a maintenance notice. Your Commission Partner activates it once your
                    plan is paid &mdash; everything you add here is saved in the meantime and goes live with it.
                </p>
            </div>
        </div>
    @endif

    @unless ($microsite)
        <div class="dash-alert">
            <span class="dash-alert__icon"><x-icon name="home" class="w-5 h-5" /></span>
            <div>
                <p class="dash-alert__title">Your business page has not been set up yet</p>
                <p class="dash-alert__text">
                    Your Commission Partner creates it for you. Visitor and enquiry figures appear here once
                    it is live &mdash; your earnings and withdrawals below work regardless.
                </p>
            </div>
        </div>
    @endunless

    <x-dash-hero :name="$user->name" :eyebrow="$microsite ? $microsite->vipPlan->name.' Member' : 'VIP Member'"
        subtitle="Here's how your business page is doing.">
        @if ($microsite)
            <x-slot:meta>
                <span class="dash-hero__chip"><x-icon name="briefcase" class="w-3.5 h-3.5" /> {{ $microsite->business_name }}</span>
                <span class="dash-hero__chip"><x-icon name="map-pin" class="w-3.5 h-3.5" /> {{ $microsite->city->name }}</span>
                <span class="dash-hero__chip"><x-icon name="star" class="w-3.5 h-3.5" /> {{ $microsite->vipPlan->name }}</span>
            </x-slot:meta>
            <x-slot:actions>
                <a href="{{ url($microsite->publicPath()) }}" target="_blank" class="dash-hero__btn dash-hero__btn--gold">
                    View Your Page <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
                <a href="{{ route('vip.page-builder.edit') }}" class="dash-hero__btn dash-hero__btn--ghost">
                    <x-icon name="sparkles" class="w-4 h-4" /> Edit page
                </a>
            </x-slot:actions>
        @endif
    </x-dash-hero>

    {{-- Earnings first: it is the one part of this page that works whether or not
         the business page exists, and it is what members come here for. --}}
    <x-dash-heading title="Earnings" subtitle="Your commission wallet" icon="rupee" tone="emerald" />
    <div class="d-grid grid-cols-1 grid-cols-sm-2 grid-cols-xl-4 gap-4 mb-8">
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
        <x-dash-heading title="Business Page" subtitle="Visitors & engagement" icon="eye" tone="slate" />
        <div class="d-grid grid-cols-2 grid-cols-md-3 grid-cols-lg-5 gap-4 mb-8">
            @foreach ($tiles as $tile)
                <x-stat-tile :label="$tile['label']" :value="$tile['value']" :icon="$tile['icon']"
                    :color="$tile['color']" :href="$tile['href'] ?? null" :cta="$tile['cta'] ?? null" />
            @endforeach

            <div class="dash-completion">
                <div class="dash-completion__ring" style="--pct: {{ (int) $stats['completion'] }}">
                    <span>{{ $stats['completion'] }}%</span>
                </div>
                <div class="min-w-0">
                    <p class="dash-completion__label">Profile Completion</p>
                    <a href="{{ route('vip.profile.edit') }}" class="dash-completion__link">Complete profile</a>
                </div>
            </div>
        </div>

        <div class="d-grid grid-cols-lg-3 gap-4 mb-8">
            <x-dash-card class="col-span-lg-2" title="Visitors" subtitle="Page views over the last 14 days" icon="eye">
                <x-apex-chart id="chart-vip-visitors" type="area" :height="290"
                    :series="[['name' => 'Visitors', 'data' => $visitorsChart['data']]]"
                    :categories="$visitorsChart['categories']" :colors="['#2c704c']" />
            </x-dash-card>
            <x-dash-card title="Engagement" subtitle="Contact button clicks" icon="chat-bubble">
                <x-apex-chart id="chart-vip-clicks" type="donut" :height="290"
                    :series="[$stats['whatsapp_clicks'], $stats['call_clicks'], $stats['direction_clicks'], $stats['website_clicks']]"
                    :labels="['WhatsApp', 'Call', 'Directions', 'Website']"
                    :colors="['#25d366', '#2c704c', '#d4af37', '#5fa97e']" />
            </x-dash-card>
        </div>

        <x-dash-heading title="Quick Actions" subtitle="Keep your page fresh" icon="sparkles" tone="gold" />
        <div class="d-grid grid-cols-md-3 gap-4 mb-8">
            <a href="{{ route('vip.profile.edit') }}" class="dash-action">
                <span class="dash-action__icon"><x-icon name="identification" class="w-5 h-5" /></span>
                <span class="min-w-0 flex-grow-1">
                    <span class="dash-action__title">Edit Business Profile</span>
                    <span class="dash-action__text">Basic info, contact, hours, social</span>
                </span>
                <x-icon name="chevron-right" class="dash-action__arrow w-4 h-4" />
            </a>
            <a href="{{ route('vip.modules.edit') }}" class="dash-action">
                <span class="dash-action__icon"><x-icon name="eye" class="w-5 h-5" /></span>
                <span class="min-w-0 flex-grow-1">
                    <span class="dash-action__title">Manage Section Visibility</span>
                    <span class="dash-action__text">Turn page sections on/off</span>
                </span>
                <x-icon name="chevron-right" class="dash-action__arrow w-4 h-4" />
            </a>
            <a href="{{ route('vip.leads.index') }}" class="dash-action">
                <span class="dash-action__icon"><x-icon name="inbox" class="w-5 h-5" /></span>
                <span class="min-w-0 flex-grow-1">
                    <span class="dash-action__title">View Leads</span>
                    <span class="dash-action__text">{{ $stats['total_leads'] }} total enquiries</span>
                </span>
                <x-icon name="chevron-right" class="dash-action__arrow w-4 h-4" />
            </a>
        </div>
    @endif

    <div class="dash-note">
        <x-icon name="sparkles" class="w-4 h-4 flex-shrink-0" />
        <span>Bookings, Offers/Coupons, Team Members, Certifications, and a Download Center ship in Phase 2.</span>
    </div>
</x-layouts.app>
