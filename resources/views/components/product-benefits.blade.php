@props(['product'])

@php
    $benefits = $product->relationLoaded('benefits')
        ? $product->benefits->where('status', 'active')
        : $product->benefits()->where('status', 'active')->orderBy('display_order')->get();
    $modalId = 'benefits-modal-'.$product->id;
@endphp

@if ($benefits->isNotEmpty())
    <button class="btn btn-link p-0" type="button" data-modal-open="#{{ $modalId }}"
            {{ $attributes->merge(['class' => 'd-inline-flex align-items-center gap-2 border border-brand-subtle text-brand px-5 py-2 rounded-pill fw-medium']) }}>
        <x-icon name="sparkles" class="w-4 h-4 text-gold" /> Benefits
    </button>

    <div id="{{ $modalId }}" data-modal
         class="d-none position-fixed top-0 start-0 w-100 h-100 z-3 align-items-center justify-content-center p-4" style="background-color: color-mix(in srgb, var(--color-brand-950) 50%, transparent)"
         role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
        <div class="bg-body rounded-4 mw-lg w-100 overflow-y-auto premium-shadow animate-fade-in-up">
            <div class="d-flex align-items-center justify-content-between gap-4 px-6 py-4 border-bottom position-sticky top-0 bg-body rounded-top">
                <h3 id="{{ $modalId }}-title" class="font-display fs-lg fw-bold text-brand-emphasis">
                    Why you'll love {{ $product->name }}
                </h3>
                <button type="button" data-modal-close aria-label="Close" class="btn btn-link p-0 text-decoration-none text-secondary">
                    <x-icon name="x-mark" class="w-6 h-6" />
                </button>
            </div>
            <div class="p-6 d-flex flex-column gap-5">
                @foreach ($benefits as $benefit)
                    <div class="d-flex align-items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-3 d-flex align-items-center justify-content-center overflow-hidden" style="background: linear-gradient(to bottom right, var(--color-brand-50), color-mix(in srgb, var(--color-gold-500) 10%, transparent))">
                            @if ($benefit->image)
                                <img src="{{ asset('storage/'.$benefit->image) }}" alt="" class="w-100 h-100 object-fit-cover">
                            @else
                                <x-icon :name="$benefit->icon ?: 'check-circle'" class="w-6 h-6 text-brand" />
                            @endif
                        </div>
                        <div>
                            <h4 class="fw-semibold text-brand-emphasis">{{ $benefit->title }}</h4>
                            @if ($benefit->description)
                                <p class="fs-sm text-body-secondary mt-1 lh-lg">{{ $benefit->description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
