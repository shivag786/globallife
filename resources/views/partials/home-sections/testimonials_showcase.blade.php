@php $testimonials = app(\App\Repositories\TestimonialRepository::class)->activeOrdered(); @endphp
@if ($testimonials->isNotEmpty())
    <div id="testimonials" class="mw-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-secondary">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-6">
            @foreach ($testimonials as $index => $testimonial)
                <div class="reveal bg-body border rounded-4 p-6 premium-shadow" style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="d-flex gap-1 mb-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-icon name="star" :filled="true" class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-gold' : 'text-body-tertiary' }}" />
                        @endfor
                    </div>
                    <p class="text-body-secondary fs-sm mb-5">&ldquo;{{ $testimonial->content }}&rdquo;</p>
                    <div class="d-flex align-items-center gap-3">
                        @if ($testimonial->photo)
                            <img src="{{ asset('storage/'.$testimonial->photo) }}" alt="{{ $testimonial->name }}" class="w-10 h-10 rounded-pill object-fit-cover">
                        @else
                            <div class="w-10 h-10 rounded-pill bg-brand-subtle text-brand d-flex align-items-center justify-content-center fs-sm fw-semibold">
                                {{ mb_substr($testimonial->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="fw-semibold text-brand-emphasis fs-sm">{{ $testimonial->name }}</p>
                            <p class="fs-xs text-secondary">{{ collect([$testimonial->role_title, $testimonial->city])->filter()->implode(', ') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
