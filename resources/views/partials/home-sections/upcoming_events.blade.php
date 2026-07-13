@php $events = app(\App\Repositories\EventRepository::class)->upcoming(); @endphp
@if ($events->isNotEmpty())
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-slate-500">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($events as $index => $event)
                <a href="{{ route('events.show', $event) }}"
                   class="reveal group block bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow hover:-translate-y-1 transition"
                   style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="aspect-[16/9] bg-brand-50 overflow-hidden">
                        @if ($event->image)
                            <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-semibold text-brand-600 uppercase tracking-wide flex items-center gap-1">
                            <x-icon name="calendar" class="w-4 h-4" /> {{ $event->event_date->format('M j, Y') }}
                        </p>
                        <h3 class="font-display font-semibold text-brand-900 mt-1">{{ $event->title }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-10 reveal">
            <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1.5 text-brand-700 font-medium hover:underline">
                View All Events <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
@endif
