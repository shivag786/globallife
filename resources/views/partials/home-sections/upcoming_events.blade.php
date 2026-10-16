@php $events = app(\App\Repositories\EventRepository::class)->upcoming(); @endphp
@if ($events->isNotEmpty())
    <div class="mw-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-secondary">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-6">
            @foreach ($events as $index => $event)
                <a href="{{ route('events.show', $event) }}"
                   class="reveal d-block bg-body border rounded-4 overflow-hidden premium-shadow"
                   style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="aspect-video bg-brand-subtle overflow-hidden">
                        @if ($event->image)
                            <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-100 h-100 object-fit-cover">
                        @endif
                    </div>
                    <div class="p-5">
                        <p class="fs-xs fw-semibold text-brand text-uppercase tracking-wide d-flex align-items-center gap-1">
                            <x-icon name="calendar" class="w-4 h-4" /> {{ $event->event_date->format('M j, Y') }}
                        </p>
                        <h3 class="font-display fw-semibold text-brand-emphasis mt-1">{{ $event->title }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-10 reveal">
            <a href="{{ route('events.index') }}" class="d-inline-flex align-items-center gap-2 text-brand fw-medium hover-underline">
                View All Events <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
@endif
