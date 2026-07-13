@php $testimonials = app(\App\Repositories\TestimonialRepository::class)->activeOrdered(); @endphp
@if ($testimonials->isNotEmpty())
    <div id="testimonials" class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-slate-500">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($testimonials as $index => $testimonial)
                <div class="reveal bg-white border border-slate-100 rounded-2xl p-6 premium-shadow" style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="flex gap-0.5 mb-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-icon name="star" :filled="true" class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-gold-500' : 'text-slate-200' }}" />
                        @endfor
                    </div>
                    <p class="text-slate-600 text-sm mb-5">&ldquo;{{ $testimonial->content }}&rdquo;</p>
                    <div class="flex items-center gap-3">
                        @if ($testimonial->photo)
                            <img src="{{ asset('storage/'.$testimonial->photo) }}" alt="{{ $testimonial->name }}" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm font-semibold">
                                {{ mb_substr($testimonial->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-brand-900 text-sm">{{ $testimonial->name }}</p>
                            <p class="text-xs text-slate-400">{{ collect([$testimonial->role_title, $testimonial->city])->filter()->implode(', ') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
