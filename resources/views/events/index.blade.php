<x-layouts.public title="Events" :metaDescription="'Upcoming training and community events from Global Life.'">
    <div class="max-w-5xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="font-display text-4xl font-bold text-brand-900 mb-3">Events</h1>
            <p class="text-slate-500">Training sessions and community events, online and in person.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-8">
            @forelse ($events as $event)
                <a href="{{ route('events.show', $event) }}" class="reveal group block bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow hover:-translate-y-1 transition">
                    <div class="aspect-[16/9] bg-brand-50 overflow-hidden">
                        @if ($event->image)
                            <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                    </div>
                    <div class="p-6">
                        <p class="text-xs font-semibold text-brand-600 uppercase tracking-wide flex items-center gap-1">
                            <x-icon name="calendar" class="w-4 h-4" /> {{ $event->event_date->format('M j, Y · g:i A') }}
                        </p>
                        <h2 class="font-display text-lg font-semibold text-brand-900 mt-2 mb-1">{{ $event->title }}</h2>
                        @if ($event->location)
                            <p class="text-sm text-slate-500 flex items-center gap-1"><x-icon name="map-pin" class="w-4 h-4" /> {{ $event->location }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <p class="col-span-full text-center text-slate-400 py-16">No events scheduled right now.</p>
            @endforelse
        </div>

        <div class="mt-12">{{ $events->links() }}</div>
    </div>
</x-layouts.public>
