@php $icons = ['shield-check', 'heart', 'rupee', 'truck']; @endphp
<div class="bg-cream-dark/50 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="font-display text-3xl font-bold text-center text-brand-900 mb-12 reveal">{{ $section->title }}</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($section->items ?? [] as $index => $item)
                <div class="reveal bg-white border border-slate-100 rounded-2xl p-6 premium-shadow hover:-translate-y-1 transition"
                     style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                        <x-icon :name="$icons[$index % count($icons)]" class="w-6 h-6" />
                    </div>
                    <h3 class="font-display font-semibold text-brand-900 mb-2">{{ $item['title'] ?? '' }}</h3>
                    <p class="text-sm text-slate-500">{{ $item['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
