<x-layouts.public :title="$event->title" :metaDescription="\Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 150)">
    <div class="mw-3xl mx-auto px-6 py-16">
        <a href="{{ route('events.index') }}" class="d-inline-flex align-items-center gap-1 fs-sm text-brand hover-underline">
            <x-icon name="arrow-right" class="w-4 h-4" /> All Events
        </a>

        @if ($event->image)
            <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-100 rounded-4 premium-shadow my-6">
        @endif

        <h1 class="font-display fs-3xl fw-bold text-brand-emphasis mb-3">{{ $event->title }}</h1>
        <div class="d-flex flex-wrap gap-4 fs-sm text-secondary mb-8">
            <span class="d-flex align-items-center gap-1"><x-icon name="calendar" class="w-4 h-4" /> {{ $event->event_date->format('l, F j, Y · g:i A') }}</span>
            @if ($event->location)
                <span class="d-flex align-items-center gap-1"><x-icon name="map-pin" class="w-4 h-4" /> {{ $event->location }}</span>
            @endif
        </div>

        @if ($event->description)
            <p class="text-body-secondary lh-lg">{{ $event->description }}</p>
        @endif
    </div>
</x-layouts.public>
