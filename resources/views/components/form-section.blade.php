@props([
    'id',
    'title',
    'summary' => null,
    'icon' => 'sparkles',
    'open' => true,
    'badge' => null,
])

{{-- One collapsible section of an x-member-form, driven by Bootstrap's collapse. --}}
<section class="member-section" data-member-section="{{ $id }}">
    <button type="button"
            class="member-section-toggle {{ $open ? '' : 'collapsed' }}"
            data-bs-toggle="collapse"
            data-bs-target="#{{ $id }}"
            aria-expanded="{{ $open ? 'true' : 'false' }}"
            aria-controls="{{ $id }}">
        <span class="member-section-icon"><x-icon :name="$icon" class="w-5 h-5" /></span>
        <span class="member-section-titles">
            <span class="member-section-title">
                {{ $title }}
                @if ($badge)
                    <span class="badge rounded-pill text-bg-light border fw-normal ms-1">{{ $badge }}</span>
                @endif
            </span>
            @if ($summary)
                <span class="member-section-summary">{{ $summary }}</span>
            @endif
        </span>
        <span class="member-section-chevron"><x-icon name="chevron-down" class="w-4 h-4" /></span>
    </button>

    <div id="{{ $id }}" class="collapse {{ $open ? 'show' : '' }}">
        <div class="member-section-body">
            {{ $slot }}
        </div>
    </div>
</section>
