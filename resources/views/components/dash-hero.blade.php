@props([
    'name',
    'eyebrow' => null,
    'subtitle' => null,
])

@php
    // The app runs in UTC; the greeting and date are for people in India.
    $now = now('Asia/Kolkata');
    $hour = (int) $now->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    // "Dr. Anjali Mehra" greets as "Dr. Anjali", not "Dr.".
    $words = preg_split('/\s+/', trim($name));
    $firstName = count($words) > 1 && str_ends_with($words[0], '.') ? $words[0].' '.$words[1] : $words[0];
@endphp

{{-- Welcome banner at the top of every panel dashboard. Decoration only:
     the optional `meta` slot holds chips, `actions` holds links. --}}
<section {{ $attributes->class('dash-hero') }}>
    <div class="dash-hero__body">
        <div class="min-w-0">
            <p class="dash-hero__eyebrow">
                <span class="dash-hero__dot"></span>
                @if ($eyebrow){{ $eyebrow }} &middot; @endif{{ $now->format('l, d M Y') }}
            </p>
            <h2 class="dash-hero__title">{{ $greeting }}, <span>{{ $firstName }}</span></h2>
            <p class="dash-hero__text">
                Welcome back, <strong>{{ $name }}</strong>.@if ($subtitle) {{ $subtitle }}@endif
            </p>
            @isset($meta)
                <div class="dash-hero__meta">{{ $meta }}</div>
            @endisset
        </div>
        @isset($actions)
            <div class="dash-hero__actions">{{ $actions }}</div>
        @endisset
    </div>
</section>
