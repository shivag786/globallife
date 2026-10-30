@if ($microsite->faqs->isNotEmpty())
    <section id="faqs" class="msite-section {{ $sectionBg ?? 'bg-cream' }}">
        <div class="msite-container mw-3xl">
            @include('partials.microsite.section-heading', ['heading' => $microsite->layout()->heading('faqs')])
            <div class="d-flex flex-column gap-3">
                @foreach ($microsite->faqs as $index => $faq)
                    <details class="msite-card p-5 reveal" style="transition-delay: {{ ($index % 5) * 0.06 }}s">
                        <summary class="font-heading fw-semibold text-brand-emphasis list-unstyled d-flex align-items-center justify-content-between gap-4">
                            {{ $faq->question }}
                            <x-icon name="chevron-right" class="w-4 h-4 text-body-tertiary open-rotate-90 flex-shrink-0" />
                        </summary>
                        <p class="fs-sm text-body-secondary mt-3 lh-1">{{ $faq->answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
