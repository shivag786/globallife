@php $icons = ['heart', 'tag', 'users', 'sparkles']; @endphp
<div class="bg-cream-dark/50 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="font-display text-3xl font-bold text-center text-brand-900 mb-12 reveal">{{ $section->title }}</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($section->items ?? [] as $index => $item)
                <div class="reveal bg-white border border-slate-100 rounded-2xl p-6 premium-shadow relative hover:-translate-y-1 transition"
                     style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-700 text-white text-sm font-bold">{{ $index + 1 }}</span>
                        <x-icon :name="$icons[$index % count($icons)]" class="w-5 h-5 text-brand-500" />
                    </div>
                    <h3 class="font-display font-semibold text-brand-900 mb-2">{{ $item['title'] ?? '' }}</h3>
                    <p class="text-sm text-slate-500">{{ $item['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
