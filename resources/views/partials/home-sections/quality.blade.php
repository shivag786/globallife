@php
    $qualityIcons = ['beaker', 'leaf', 'shield-check', 'sparkles', 'heart', 'academic-cap'];
    $pillars = $section->items ?? [];
@endphp
<section class="bg-gradient-to-b from-cream to-cream-dark/30">
    <div class="max-w-6xl mx-auto px-6 py-16 lg:py-24">
        <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
            <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Quality &amp; Transparency</p>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-brand-900">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-slate-500 mt-3 text-lg">{{ $section->subtitle }}</p>
            @endif
        </div>

        @if ($section->image_path)
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="reveal" data-reveal="left">
                    <img src="{{ asset('storage/'.$section->image_path) }}" alt="{{ $section->title }}"
                         class="w-full h-80 lg:h-[26rem] object-cover rounded-3xl premium-shadow">
                </div>
                <div class="space-y-6">
                    @foreach ($pillars as $index => $pillar)
                        <div class="reveal flex gap-4" data-reveal="right" style="transition-delay: {{ ($index % 5) * 0.1 }}s">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-white border border-brand-100 flex items-center justify-center premium-shadow">
                                <x-icon name="{{ $qualityIcons[$index % count($qualityIcons)] }}" class="w-6 h-6 text-brand-600" />
                            </div>
                            <div>
                                <p class="font-display font-semibold text-brand-900">{{ $pillar['title'] ?? '' }}</p>
                                @if ($pillar['description'] ?? null)
                                    <p class="text-sm text-slate-500 mt-1 leading-relaxed">{{ $pillar['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($pillars as $index => $pillar)
                    <div class="reveal bg-white rounded-2xl p-6 border border-slate-100 premium-shadow hover:-translate-y-1 transition duration-300"
                         style="transition-delay: {{ ($index % 4) * 0.1 }}s">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 flex items-center justify-center mb-4">
                            <x-icon name="{{ $qualityIcons[$index % count($qualityIcons)] }}" class="w-6 h-6 text-white" />
                        </div>
                        <p class="font-display font-semibold text-brand-900 mb-1">{{ $pillar['title'] ?? '' }}</p>
                        @if ($pillar['description'] ?? null)
                            <p class="text-sm text-slate-500 leading-relaxed">{{ $pillar['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
