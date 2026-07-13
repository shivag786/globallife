@php $cities = \App\Models\City::where('status', 'active')->orderBy('name')->get(); @endphp
@if ($cities->isNotEmpty())
    <div class="bg-cream-dark/50 py-16">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <div class="reveal">
                <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
                @if ($section->subtitle)
                    <p class="text-slate-500 mb-10">{{ $section->subtitle }}</p>
                @endif
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                @foreach ($cities as $index => $city)
                    <span class="reveal inline-flex items-center gap-1.5 bg-white border border-slate-100 text-brand-800 text-sm font-medium px-4 py-2 rounded-full premium-shadow"
                          style="transition-delay: {{ min($index * 0.04, 0.6) }}s">
                        <x-icon name="map-pin" class="w-4 h-4 text-brand-500" /> {{ $city->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
@endif
