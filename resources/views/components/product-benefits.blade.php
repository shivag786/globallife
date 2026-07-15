@props(['product'])

@php
    $benefits = $product->relationLoaded('benefits')
        ? $product->benefits->where('status', 'active')
        : $product->benefits()->where('status', 'active')->orderBy('display_order')->get();
    $modalId = 'benefits-modal-'.$product->id;
@endphp

@if ($benefits->isNotEmpty())
    <button type="button" data-modal-open="#{{ $modalId }}"
            {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 border border-brand-200 text-brand-700 px-5 py-2.5 rounded-full font-medium hover:bg-brand-50 transition']) }}>
        <x-icon name="sparkles" class="w-4 h-4 text-gold-500" /> Benefits
    </button>

    <div id="{{ $modalId }}" data-modal
         class="hidden fixed inset-0 z-50 items-center justify-center bg-brand-950/50 backdrop-blur-sm p-4"
         role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
        <div class="bg-white rounded-2xl max-w-lg w-full max-h-[85vh] overflow-y-auto premium-shadow animate-fade-in-up">
            <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-slate-100 sticky top-0 bg-white rounded-t-2xl">
                <h3 id="{{ $modalId }}-title" class="font-display text-lg font-bold text-brand-900">
                    Why you'll love {{ $product->name }}
                </h3>
                <button type="button" data-modal-close aria-label="Close" class="text-slate-400 hover:text-slate-700 transition">
                    <x-icon name="x-mark" class="w-6 h-6" />
                </button>
            </div>
            <div class="p-6 space-y-5">
                @foreach ($benefits as $benefit)
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-brand-50 to-gold-500/10 flex items-center justify-center overflow-hidden">
                            @if ($benefit->image)
                                <img src="{{ asset('storage/'.$benefit->image) }}" alt="" class="w-full h-full object-cover">
                            @else
                                <x-icon :name="$benefit->icon ?: 'check-circle'" class="w-6 h-6 text-brand-600" />
                            @endif
                        </div>
                        <div>
                            <h4 class="font-semibold text-brand-900">{{ $benefit->title }}</h4>
                            @if ($benefit->description)
                                <p class="text-sm text-slate-600 mt-0.5 leading-relaxed">{{ $benefit->description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
