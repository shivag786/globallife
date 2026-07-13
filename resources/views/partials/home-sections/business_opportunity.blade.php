@php $icons = ['academic-cap', 'megaphone', 'users', 'heart']; @endphp
<div id="opportunity" class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-center mb-12 reveal">
        <h2 class="font-display text-3xl font-bold text-brand-900 mb-3">{{ $section->title }}</h2>
        @if ($section->content)
            <div class="editor-content max-w-2xl mx-auto text-left sm:text-center">{!! $section->content !!}</div>
        @endif
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($section->items ?? [] as $index => $item)
            <div class="reveal bg-brand-50 rounded-2xl p-6 hover:bg-brand-100/70 transition" style="transition-delay: {{ $index * 0.1 }}s">
                <div class="w-11 h-11 rounded-xl bg-white text-brand-600 flex items-center justify-center mb-4 premium-shadow">
                    <x-icon :name="$icons[$index % count($icons)]" class="w-6 h-6" />
                </div>
                <h3 class="font-display font-semibold text-brand-900 mb-2">{{ $item['title'] ?? '' }}</h3>
                <p class="text-sm text-slate-500">{{ $item['description'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
    @if ($section->cta_label && $section->cta_url)
        <div class="text-center mt-10 reveal">
            <a href="{{ $section->cta_url }}" class="inline-flex items-center gap-2 bg-brand-700 text-white px-8 py-3 rounded-full font-medium hover:bg-brand-800 hover:-translate-y-0.5 transition">
                {{ $section->cta_label }} <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    @endif
</div>
