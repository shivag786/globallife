@php $items = app(\App\Repositories\MediaRepository::class)->activeOrdered(); @endphp
@if ($items->isNotEmpty())
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-slate-500">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($items as $index => $item)
                <div class="reveal aspect-square rounded-2xl overflow-hidden premium-shadow" style="transition-delay: {{ $index * 0.05 }}s">
                    <img src="{{ asset('storage/'.$item->file_path) }}" alt="{{ $item->alt_text ?? $item->title }}" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                </div>
            @endforeach
        </div>
    </div>
@endif
