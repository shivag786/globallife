@php
    $avatarPalette = ['bg-brand-100 text-brand-700', 'bg-gold-400/20 text-gold-600', 'bg-brand-700 text-white'];
@endphp
<div class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-center mb-12 reveal">
        <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
        @if ($section->subtitle)
            <p class="text-slate-500">{{ $section->subtitle }}</p>
        @endif
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($section->items ?? [] as $index => $member)
            <div class="team-card bg-white border border-slate-100 rounded-2xl p-6 text-center premium-shadow">
                <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center text-2xl font-bold mb-4 {{ $avatarPalette[$index % 3] }}">
                    {{ mb_substr($member['name'] ?? '', 0, 1) }}
                </div>
                <p class="font-display font-semibold text-brand-900">{{ $member['name'] ?? '' }}</p>
                <p class="text-sm text-gold-600 font-medium mt-1">{{ $member['role'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
</div>
