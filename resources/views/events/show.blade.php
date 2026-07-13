<x-layouts.public :title="$event->title" :metaDescription="\Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 150)">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">
            <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> All Events
        </a>

        @if ($event->image)
            <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full rounded-2xl premium-shadow my-6">
        @endif

        <h1 class="font-display text-3xl font-bold text-brand-900 mb-3">{{ $event->title }}</h1>
        <div class="flex flex-wrap gap-4 text-sm text-slate-500 mb-8">
            <span class="flex items-center gap-1"><x-icon name="calendar" class="w-4 h-4" /> {{ $event->event_date->format('l, F j, Y · g:i A') }}</span>
            @if ($event->location)
                <span class="flex items-center gap-1"><x-icon name="map-pin" class="w-4 h-4" /> {{ $event->location }}</span>
            @endif
        </div>

        @if ($event->description)
            <p class="text-slate-600 whitespace-pre-line leading-relaxed">{{ $event->description }}</p>
        @endif
    </div>
</x-layouts.public>
