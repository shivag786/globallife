@if ($banners->isNotEmpty())
    <div class="position-relative overflow-hidden bg-brand" data-hero-slider>
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex" data-hero-track>
            @foreach ($banners as $slide)
                <div class="position-relative w-100 h-100 flex-shrink-0">
                    <img src="{{ asset('storage/'.$slide->image_path) }}" alt="{{ $slide->heading }}" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to right, color-mix(in srgb, var(--color-brand-950) 85%, transparent), color-mix(in srgb, var(--color-brand-950) 40%, transparent), transparent)"></div>

                    <div class="position-relative h-100 msite-container d-flex align-items-center">
                        <div class="mw-xl reveal" data-reveal="left">
                            @if ($slide->heading)
                                <h1 class="msite-heading fs-4xl fs-5xl text-white mb-4">{{ $slide->heading }}</h1>
                            @endif
                            @if ($slide->subheading)
                                <p class="text-brand fs-lg mb-8 mw-lg">{{ $slide->subheading }}</p>
                            @endif
                            <div class="d-flex flex-wrap align-items-center gap-4">
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
                    class="btn btn-link p-0 text-decoration-none position-absolute start-4 top-50 w-11 h-11 rounded-pill msite-glass text-brand-emphasis d-flex align-items-center justify-content-center">
                <x-icon name="chevron-left" class="w-5 h-5" />
            </button>
            <button type="button" data-hero-next aria-label="Next slide"
                    class="btn btn-link p-0 text-decoration-none position-absolute end-4 top-50 w-11 h-11 rounded-pill msite-glass text-brand-emphasis d-flex align-items-center justify-content-center">
                <x-icon name="chevron-right" class="w-5 h-5" />
            </button>

            <div class="position-absolute bottom-6 start-50 d-flex gap-2" data-hero-dots>
                @foreach ($banners as $index => $slide)
                    <button type="button" data-hero-dot="{{ $index }}" aria-label="Go to slide {{ $index + 1 }}"
                            class="btn btn-link p-0 text-decoration-none w-2.5 h-2.5 rounded-pill bg-white bg-opacity-50"></button>
                @endforeach
            </div>
        @endif
    </div>
@endif
