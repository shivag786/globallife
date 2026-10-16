@php $cities = \App\Models\City::where('status', 'active')->orderBy('name')->get(); @endphp
@if ($cities->isNotEmpty())
    <div class="bg-cream py-16">
        <div class="mw-5xl mx-auto px-6 text-center">
            <div class="reveal">
                <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
                @if ($section->subtitle)
                    <p class="text-secondary mb-10">{{ $section->subtitle }}</p>
                @endif
            </div>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                @foreach ($cities as $index => $city)
                    <span class="reveal d-inline-flex align-items-center gap-2 bg-body border text-brand-emphasis fs-sm fw-medium px-4 py-2 rounded-pill premium-shadow"
                          style="transition-delay: {{ min($index * 0.04, 0.6) }}s">
                        <x-icon name="map-pin" class="w-4 h-4 text-brand" /> {{ $city->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
@endif
