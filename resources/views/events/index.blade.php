<x-layouts.public title="Events" :metaDescription="'Upcoming training and community events from Global Life.'">
    <div class="mw-5xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="font-display fs-4xl fw-bold text-brand-emphasis mb-3">Events</h1>
            <p class="text-secondary">Training sessions and community events, online and in person.</p>
        </div>

        <div class="d-grid grid-cols-sm-2 gap-8">
            @forelse ($events as $event)
                <a href="{{ route('events.show', $event) }}" class="reveal d-block bg-body border rounded-4 overflow-hidden premium-shadow">
                    <div class="aspect-video bg-brand-subtle overflow-hidden">
                        @if ($event->image)
                            <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-100 h-100 object-fit-cover">
                        @endif
                    </div>
                    <div class="p-6">
                        <p class="fs-xs fw-semibold text-brand text-uppercase tracking-wide d-flex align-items-center gap-1">
                            <x-icon name="calendar" class="w-4 h-4" /> {{ $event->event_date->format('M j, Y · g:i A') }}
                        </p>
                        <h2 class="font-display fs-lg fw-semibold text-brand-emphasis mt-2 mb-1">{{ $event->title }}</h2>
                        @if ($event->location)
                            <p class="fs-sm text-secondary d-flex align-items-center gap-1"><x-icon name="map-pin" class="w-4 h-4" /> {{ $event->location }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <p class="col-span-full text-center text-secondary py-16">No events scheduled right now.</p>
            @endforelse
        </div>

        <div class="mt-12">{{ $events->links() }}</div>
    </div>
</x-layouts.public>
