@php $items = app(\App\Repositories\MediaRepository::class)->activeOrdered(); @endphp
@if ($items->isNotEmpty())
    <div class="mw-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-secondary">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="d-grid grid-cols-2 grid-cols-sm-3 grid-cols-lg-4 gap-4">
            @foreach ($items as $index => $item)
                <div class="reveal aspect-square rounded-4 overflow-hidden premium-shadow" style="transition-delay: {{ $index * 0.05 }}s">
                    <img src="{{ asset('storage/'.$item->file_path) }}" alt="{{ $item->alt_text ?? $item->title }}" class="w-100 h-100 object-fit-cover">
                </div>
            @endforeach
        </div>
    </div>
@endif
