@if ($microsite->galleryItems->isNotEmpty())
    <section id="gallery" class="msite-section bg-cream-dark/30">
        <div class="msite-container">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Take a Look</p>
                <h2 class="msite-heading text-3xl md:text-4xl">Gallery</h2>
            </div>
            <div class="columns-2 sm:columns-3 lg:columns-4 gap-4" data-msite-gallery>
                @foreach ($microsite->galleryItems as $index => $item)
                    <button type="button" data-msite-gallery-item="{{ $index }}"
                            class="block w-full mb-4 rounded-xl overflow-hidden msite-card-img reveal">
                        <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->title }}" loading="lazy"
                             class="w-full h-auto object-cover hover:scale-105 transition duration-500">
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <div id="msite-lightbox" class="fixed inset-0 z-50 hidden bg-black/90 items-center justify-center p-4" data-msite-lightbox>
        <button type="button" data-msite-lightbox-close aria-label="Close" class="absolute top-5 right-5 text-white/80 hover:text-white">
            <x-icon name="x-mark" class="w-8 h-8" />
        </button>
        <button type="button" data-msite-lightbox-prev aria-label="Previous" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white">
            <x-icon name="chevron-left" class="w-9 h-9" />
        </button>
        <img data-msite-lightbox-img src="" alt="" class="max-h-[85vh] max-w-full rounded-lg object-contain">
        <button type="button" data-msite-lightbox-next aria-label="Next" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white">
            <x-icon name="chevron-right" class="w-9 h-9" />
        </button>
    </div>

    @php
        $galleryImageUrls = $microsite->galleryItems->map(fn ($item) => asset('storage/'.$item->image_path))->all();
    @endphp
    <script id="msite-gallery-data" type="application/json">{{ json_encode($galleryImageUrls) }}</script>
@endif
