@if ($microsite->description || $microsite->short_description)
    @php
        $stats = array_filter([
            $microsite->establishment_year ? [now()->year - $microsite->establishment_year, 'Years Serving'] : null,
            $microsite->services->count() ? [$microsite->services->count(), 'Services'] : null,
            $microsite->products->count() ? [$microsite->products->count(), 'Products'] : null,
            $microsite->reviews->count() ? [$microsite->reviews->count(), 'Reviews'] : null,
        ]);
    @endphp
    <section id="about" class="msite-section">
        <div class="msite-container d-grid grid-cols-lg-2 gap-14 align-items-center">
            <div class="reveal" data-reveal="left">
                @if ($microsite->cover_banner_path)
                    <img src="{{ asset('storage/'.$microsite->cover_banner_path) }}" alt="{{ $microsite->business_name }}"
                         class="w-100 h-80 object-fit-cover rounded-4 premium-shadow animate-float-y">
                @elseif ($microsite->logo_path)
                    <div class="d-flex align-items-center justify-content-center h-80 bg-brand-subtle rounded-4">
                        <img src="{{ asset('storage/'.$microsite->logo_path) }}" alt="{{ $microsite->business_name }}"
                             class="w-40 h-40 object-fit-cover rounded-pill premium-shadow animate-float-y">
                    </div>
                @endif
            </div>

            <div class="reveal" data-reveal="right">
                <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">About Us</p>
                <h2 class="msite-heading fs-3xl fs-4xl mb-5">About {{ $microsite->business_name }}</h2>
                @if ($microsite->short_description)
                    <p class="fs-lg text-body-secondary mb-4">{{ $microsite->short_description }}</p>
                @endif
                @if ($microsite->description)
                    <p class="text-body-secondary lh-1 mb-8">{{ $microsite->description }}</p>
                @endif

                @php
                    $statsGridClass = match (count($stats)) {
                        1 => 'grid-cols-1',
                        2 => 'grid-cols-2',
                        3 => 'grid-cols-3',
                        default => 'grid-cols-4',
                    };
                @endphp
                @if (!empty($stats))
                    <div class="d-grid {{ $statsGridClass }} gap-6 border-top pt-6">
                        @foreach ($stats as [$value, $label])
                            <div>
                                <p class="fs-2xl fs-3xl fw-bolder text-brand">{{ $value }}+</p>
                                <p class="fs-xs text-secondary text-uppercase tracking-wide mt-1">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
