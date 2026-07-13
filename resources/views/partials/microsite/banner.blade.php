@if ($banners->isNotEmpty())
    <div class="relative overflow-hidden h-[420px] md:h-[650px] lg:h-[720px] bg-brand-950" data-hero-slider>
        <div class="absolute inset-0 flex transition-transform duration-700 ease-out" data-hero-track>
            @foreach ($banners as $slide)
                <div class="relative w-full h-full flex-shrink-0">
                    <img src="{{ asset('storage/'.$slide->image_path) }}" alt="{{ $slide->heading }}" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-brand-950/85 via-brand-950/40 to-transparent"></div>

                    <div class="relative h-full msite-container flex items-center">
                        <div class="max-w-xl reveal" data-reveal="left">
                            @if ($slide->heading)
                                <h1 class="msite-heading text-4xl md:text-5xl lg:text-[3.25rem] text-white mb-4">{{ $slide->heading }}</h1>
                            @endif
                            @if ($slide->subheading)
                                <p class="text-brand-100 text-lg mb-8 max-w-lg">{{ $slide->subheading }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-4">
                                @if ($slide->button_text && $slide->button_link)
                                    <a href="{{ $slide->button_link }}" class="msite-btn msite-btn-primary">
                                        {{ $slide->button_text }} <x-icon name="arrow-right" class="w-4 h-4" />
                                    </a>
                                @endif
                                <a href="#enquiry" class="msite-btn msite-btn-secondary">Get in Touch</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($banners->count() > 1)
            <button type="button" data-hero-prev aria-label="Previous slide"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full msite-glass text-brand-900 flex items-center justify-center hover:scale-110 transition">
                <x-icon name="chevron-left" class="w-5 h-5" />
            </button>
            <button type="button" data-hero-next aria-label="Next slide"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full msite-glass text-brand-900 flex items-center justify-center hover:scale-110 transition">
                <x-icon name="chevron-right" class="w-5 h-5" />
            </button>

            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2" data-hero-dots>
                @foreach ($banners as $index => $slide)
                    <button type="button" data-hero-dot="{{ $index }}" aria-label="Go to slide {{ $index + 1 }}"
                            class="w-2.5 h-2.5 rounded-full bg-white/40 hover:bg-white/70 transition"></button>
                @endforeach
            </div>
        @endif
    </div>
@endif
