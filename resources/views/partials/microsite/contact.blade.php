@php
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $hours = $microsite->business_hours ?? [];

    // Check whether any contact/location information is available
    $hasContactDetails =
        !empty($microsite->address) ||
        !empty($microsite->phone_number) ||
        !empty($microsite->business_email) ||
        !empty($microsite->website_url) ||
        !empty($microsite->google_map_url);

    // Check whether business hours actually contain usable data
    $hasBusinessHours = !empty(array_filter($hours));
@endphp

<section id="contact" class="msite-section">
    <div class="msite-container">

        <div class="text-center mw-2xl mx-auto mb-14 reveal" data-reveal="zoom">
            <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">
                Get In Touch
            </p>

            <h2 class="msite-heading fs-3xl fs-4xl">
                Contact Details
            </h2>
        </div>

        @if ($hasContactDetails || $hasBusinessHours)

            {{-- Two boxes: 2-column grid | One box: centered single column --}}
            <div class="{{ $hasContactDetails && $hasBusinessHours ? 'd-grid grid-cols-lg-2 gap-8' : 'mw-xl mx-auto' }}">

                {{-- LEFT CONTACT BOX --}}
                @if ($hasContactDetails)
                    <div class="msite-card p-8 reveal" data-reveal="left">

                        <div class="d-flex flex-column gap-4 fs-sm mb-8">

                            {{-- Address --}}
                            @if ($microsite->address)
                                <div class="d-flex align-items-start gap-3">
                                    <div class="w-9 h-9 rounded-pill bg-brand-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                                        <x-icon name="map-pin" class="w-4 h-4 text-brand" />
                                    </div>

                                    <span class="pt-2">
                                        {{ $microsite->address }}
                                    </span>
                                </div>
                            @endif

                            {{-- Phone --}}
                            @if ($microsite->phone_number)
                                <div class="d-flex align-items-start gap-3">
                                    <div class="w-9 h-9 rounded-pill bg-brand-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                                        <x-icon name="phone" class="w-4 h-4 text-brand" />
                                    </div>

                                    <a href="{{ route('microsite.click', [$microsite, 'call']) }}"
                                       class="pt-2">
                                        {{ $microsite->phone_number }}
                                    </a>
                                </div>
                            @endif

                            {{-- Email --}}
                            @if ($microsite->business_email)
                                <div class="d-flex align-items-start gap-3">
                                    <div class="w-9 h-9 rounded-pill bg-brand-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                                        <x-icon name="envelope" class="w-4 h-4 text-brand" />
                                    </div>

                                    <a href="mailto:{{ $microsite->business_email }}"
                                       class="pt-2">
                                        {{ $microsite->business_email }}
                                    </a>
                                </div>
                            @endif

                            {{-- Website --}}
                            @if ($microsite->website_url)
                                <div class="d-flex align-items-start gap-3">
                                    <div class="w-9 h-9 rounded-pill bg-brand-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                                        <x-icon name="share" class="w-4 h-4 text-brand" />
                                    </div>

                                    <a href="{{ route('microsite.click', [$microsite, 'website']) }}"
                                       target="_blank"
                                       class="pt-2">
                                        {{ $microsite->website_url }}
                                    </a>
                                </div>
                            @endif

                        </div>

                        {{-- Directions --}}
                        @if ($microsite->google_map_url)
                            <div class="d-flex flex-wrap gap-3">
                                <a href="{{ route('microsite.click', [$microsite, 'direction']) }}"
                                   class="msite-btn msite-btn-primary">
                                    <x-icon name="map-pin" class="w-4 h-4" />
                                    Get Directions
                                </a>
                            </div>

                            {{-- Google Map --}}
                            <div class="mt-8 rounded-3 overflow-hidden h-56 border">
                                <iframe
                                    src="https://maps.google.com/maps?q={{ urlencode($microsite->address ?: $microsite->business_name) }}&output=embed"
                                    class="w-100 h-100"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        @endif

                    </div>
                @endif


                {{-- BUSINESS HOURS --}}
                @if ($hasBusinessHours)
                    <div class="msite-card p-8 reveal"
                         data-reveal="right">

                        <h3 class="font-heading fw-bold text-brand-emphasis mb-5 fs-lg">
                            Business Hours
                        </h3>

                        <div class="fs-sm divide-y">

                            @foreach ($days as $day)
                                <div class="d-flex justify-content-between py-2">

                                    <span class="text-body-secondary text-capitalize fw-medium">
                                        {{ $day }}
                                    </span>

                                    <span class="text-secondary">
                                        {{ ($hours[$day]['closed'] ?? false)
                                            ? 'Closed'
                                            : (($hours[$day]['open'] ?? '') . ' – ' . ($hours[$day]['close'] ?? ''))
                                        }}
                                    </span>

                                </div>
                            @endforeach

                        </div>
                    </div>
                @endif

            </div>

        @endif

    </div>
</section>