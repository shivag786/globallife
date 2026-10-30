{{-- An owner-built card section ("Meet Our Doctors", "Facilities"...).
     style "people" shows round photos; "cards" shows an image on top. --}}
@php $cards = $section->cards; @endphp
@if ($section->is_active && $cards->isNotEmpty())
    @php $people = $section->style === 'people'; @endphp
    <section id="section-{{ $section->id }}" class="msite-section {{ $sectionBg ?? '' }}">
        <div class="msite-container">
            @include('partials.microsite.section-heading', ['heading' => [
                'eyebrow' => (string) $section->eyebrow,
                'title' => $section->title,
                'subtitle' => (string) $section->subtitle,
            ]])

            <div class="d-grid grid-cols-sm-2 {{ $people ? 'grid-cols-lg-4' : 'grid-cols-lg-3' }} gap-6">
                @foreach ($cards as $card)
                    @php $modalId = 'card-detail-'.$card->id; @endphp
                    <div class="msite-card overflow-hidden d-flex flex-column h-100 reveal {{ $people ? 'text-center p-6' : 'msite-card-img' }}"
                         style="transition-delay: {{ ($loop->index % 4) * 0.08 }}s">
                        @if ($people)
                            <div class="w-32 h-32 mx-auto rounded-pill overflow-hidden bg-brand-subtle border border-4 border-white premium-shadow">
                                @if ($card->image_path)
                                    <img src="{{ asset('storage/'.$card->image_path) }}" alt="{{ $card->title }}" loading="lazy" class="w-100 h-100 object-fit-cover">
                                @else
                                    <span class="w-100 h-100 d-flex align-items-center justify-content-center">
                                        <x-icon name="user" class="w-12 h-12 text-brand" />
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="position-relative msite-card-img h-52 bg-brand-subtle">
                                @if ($card->image_path)
                                    <img src="{{ asset('storage/'.$card->image_path) }}" alt="{{ $card->title }}" loading="lazy" class="w-100 h-100 object-fit-cover">
                                @else
                                    <span class="w-100 h-100 d-flex align-items-center justify-content-center">
                                        <x-icon name="sparkles" class="w-10 h-10 text-brand" />
                                    </span>
                                @endif
                            </div>
                        @endif

                        <div class="{{ $people ? 'mt-4' : 'p-6' }} d-flex flex-column flex-grow-1">
                            <p class="font-heading fw-bold fs-lg text-brand-emphasis">{{ $card->title }}</p>
                            @if ($card->subtitle)
                                <p class="fs-xs fw-semibold text-brand text-uppercase tracking-wide mt-1">{{ $card->subtitle }}</p>
                            @endif
                            @if ($card->short_description)
                                <p class="fs-sm text-secondary mt-2 line-clamp-3">{{ $card->short_description }}</p>
                            @endif

                            <div class="mt-auto pt-4 d-flex flex-wrap gap-3 align-items-center {{ $people ? 'justify-content-center' : '' }}">
                                @if ($card->long_description)
                                    <button type="button" data-modal-open="#{{ $modalId }}" class="btn btn-link p-0 fs-sm fw-semibold text-brand text-decoration-none">
                                        Read more →
                                    </button>
                                @endif
                                @if ($card->button_label && $card->button_url)
                                    <a href="{{ $card->button_url }}" class="msite-btn msite-btn-primary h-10 px-5 fs-sm"
                                       @if (str_starts_with($card->button_url, 'http')) target="_blank" rel="noopener" @endif>
                                        {{ $card->button_label }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($card->long_description)
                        <x-microsite.detail-modal :id="$modalId" :title="$card->title" :subtitle="$card->subtitle"
                                                  :image="$card->image_path" :short="$card->short_description" :long="$card->long_description" />
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
