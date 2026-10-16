@php
    $avatarPalette = ['bg-brand-subtle text-brand', 'bg-gold-subtle text-gold', 'bg-brand text-white'];
@endphp
<div class="mw-6xl mx-auto px-6 py-16">
    <div class="text-center mb-12 reveal">
        <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
        @if ($section->subtitle)
            <p class="text-secondary">{{ $section->subtitle }}</p>
        @endif
    </div>

    <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
        @foreach ($section->items ?? [] as $index => $member)
            <div class="team-card bg-body border rounded-4 p-6 text-center premium-shadow">
                <div class="w-20 h-20 mx-auto rounded-pill d-flex align-items-center justify-content-center fs-2xl fw-bold mb-4 {{ $avatarPalette[$index % 3] }}">
                    {{ mb_substr($member['name'] ?? '', 0, 1) }}
                </div>
                <p class="font-display fw-semibold text-brand-emphasis">{{ $member['name'] ?? '' }}</p>
                <p class="fs-sm text-gold fw-medium mt-1">{{ $member['role'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
</div>
