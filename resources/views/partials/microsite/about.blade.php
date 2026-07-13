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
        <div class="msite-container grid lg:grid-cols-2 gap-14 items-center">
            <div class="reveal" data-reveal="left">
                @if ($microsite->cover_banner_path)
                    <img src="{{ asset('storage/'.$microsite->cover_banner_path) }}" alt="{{ $microsite->business_name }}"
                         class="w-full h-80 lg:h-[26rem] object-cover rounded-2xl premium-shadow animate-float-y">
                @elseif ($microsite->logo_path)
                    <div class="flex items-center justify-center h-80 lg:h-[26rem] bg-brand-50 rounded-2xl">
                        <img src="{{ asset('storage/'.$microsite->logo_path) }}" alt="{{ $microsite->business_name }}"
                             class="w-40 h-40 object-cover rounded-full premium-shadow animate-float-y">
                    </div>
                @endif
            </div>

            <div class="reveal" data-reveal="right">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">About Us</p>
                <h2 class="msite-heading text-3xl md:text-4xl mb-5">About {{ $microsite->business_name }}</h2>
                @if ($microsite->short_description)
                    <p class="text-lg text-slate-700 mb-4">{{ $microsite->short_description }}</p>
                @endif
                @if ($microsite->description)
                    <p class="text-slate-600 leading-[1.7] whitespace-pre-line mb-8">{{ $microsite->description }}</p>
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
                    <div class="grid {{ $statsGridClass }} gap-6 border-t border-slate-100 pt-6">
                        @foreach ($stats as [$value, $label])
                            <div>
                                <p class="text-2xl md:text-3xl font-extrabold text-brand-700">{{ $value }}+</p>
                                <p class="text-xs text-slate-400 uppercase tracking-wide mt-1">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
