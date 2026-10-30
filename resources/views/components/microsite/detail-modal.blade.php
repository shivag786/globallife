@props(['id', 'title', 'subtitle' => null, 'image' => null, 'price' => null, 'short' => null, 'long' => null])

{{-- "Read more" for a microsite card: image, title and long description in the
     generic [data-modal] overlay (resources/js/app.js opens and closes it). The
     slot holds the card's buttons, so ordering works from inside it too. --}}
<div id="{{ $id }}" data-modal role="dialog" aria-modal="true" aria-label="{{ $title }}"
     class="position-fixed top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center p-4 bg-black bg-opacity-50"
     style="z-index: 1070">
    <div class="bg-body rounded-4 overflow-hidden shadow-lg w-100 mw-2xl d-flex flex-column" style="max-height: 90vh">
        @if ($image)
            <img src="{{ asset('storage/'.$image) }}" alt="{{ $title }}" class="w-100 object-fit-cover flex-shrink-0" style="max-height: 18rem">
        @endif
        <div class="p-6 overflow-y-auto">
            <div class="d-flex align-items-start justify-content-between gap-4">
                <div>
                    @if ($subtitle)
                        <p class="fs-xs fw-semibold text-brand text-uppercase tracking-wide mb-1">{{ $subtitle }}</p>
                    @endif
                    <h3 class="msite-heading fs-2xl">{{ $title }}</h3>
                </div>
                <button type="button" data-modal-close class="btn btn-link p-0 text-secondary flex-shrink-0" aria-label="Close">
                    <x-icon name="x-mark" class="w-6 h-6" />
                </button>
            </div>
            @if ($price)
                <div class="mt-3">{{ $price }}</div>
            @endif
            @if ($short)
                <p class="fs-lg text-body-secondary mt-4">{{ $short }}</p>
            @endif
            @if ($long)
                <div class="text-body-secondary mt-4 text-prewrap">{{ $long }}</div>
            @endif
            @if ($slot->isNotEmpty())
                <div class="mt-6">{{ $slot }}</div>
            @endif
        </div>
    </div>
</div>
