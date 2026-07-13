@if ($microsite->faqs->isNotEmpty())
    <section id="faqs" class="msite-section bg-cream-dark/30">
        <div class="msite-container max-w-3xl">
            <div class="text-center mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Questions</p>
                <h2 class="msite-heading text-3xl md:text-4xl">Frequently Asked Questions</h2>
            </div>
            <div class="space-y-3">
                @foreach ($microsite->faqs as $index => $faq)
                    <details class="msite-card p-5 group reveal" style="transition-delay: {{ ($index % 5) * 0.06 }}s">
                        <summary class="font-heading font-semibold text-brand-950 cursor-pointer list-none flex items-center justify-between gap-4">
                            {{ $faq->question }}
                            <x-icon name="chevron-right" class="w-4 h-4 text-slate-300 group-open:rotate-90 transition-transform flex-shrink-0" />
                        </summary>
                        <p class="text-sm text-slate-600 mt-3 leading-[1.7]">{{ $faq->answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
