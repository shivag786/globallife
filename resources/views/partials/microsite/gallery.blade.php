@if ($microsite->galleryItems->isNotEmpty())
    <section id="gallery" class="msite-section bg-cream">
        <div class="msite-container">
            <div class="text-center mw-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">Take a Look</p>
                <h2 class="msite-heading fs-3xl fs-4xl">Gallery</h2>
            </div>
            <div class="columns-2 columns-sm-3 columns-lg-4 gap-4" data-msite-gallery>
                @foreach ($microsite->galleryItems as $index => $item)
                    <button type="button" data-msite-gallery-item="{{ $index }}"
                            class="btn btn-link p-0 text-decoration-none d-block w-100 mb-4 rounded-3 overflow-hidden msite-card-img reveal">
                        <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->title }}" loading="lazy"
                             class="w-100 h-auto object-fit-cover">
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <div id="msite-lightbox" class="position-fixed top-0 start-0 w-100 h-100 z-overlay d-none bg-black bg-opacity-100 align-items-center justify-content-center p-4" data-msite-lightbox>
        <button type="button" data-msite-lightbox-close aria-label="Close" class="btn btn-link p-0 text-decoration-none position-absolute top-5 end-5 text-white text-opacity-75">
            <x-icon name="x-mark" class="w-8 h-8" />
        </button>
        <button type="button" data-msite-lightbox-prev aria-label="Previous" class="btn btn-link p-0 text-decoration-none position-absolute start-4 top-50 text-white text-opacity-75">
            <x-icon name="chevron-left" class="w-9 h-9" />
        </button>
        <img data-msite-lightbox-img src="" alt="" class="mw-100 rounded-2 object-fit-contain">
        <button type="button" data-msite-lightbox-next aria-label="Next" class="btn btn-link p-0 text-decoration-none position-absolute end-4 top-50 text-white text-opacity-75">
            <x-icon name="chevron-right" class="w-9 h-9" />
        </button>
    </div>

    @php
        $galleryImageUrls = $microsite->galleryItems->map(fn ($item) => asset('storage/'.$item->image_path))->all();
    @endphp
    <script id="msite-gallery-data" type="application/json">{{ json_encode($galleryImageUrls) }}</script>
@endif
