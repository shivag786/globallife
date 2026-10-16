@php
    $links = collect([
        'facebook_url' => 'FB', 'instagram_url' => 'IG', 'youtube_url' => 'YT',
        'linkedin_url' => 'IN', 'twitter_url' => 'X', 'telegram_url' => 'TG', 'pinterest_url' => 'PN',
    ])->filter(fn ($label, $field) => $microsite->{$field});
@endphp
@if ($links->isNotEmpty())
    <section class="pb-20 text-center reveal">
        <p class="fs-sm text-secondary mb-4">Follow {{ $microsite->business_name }}</p>
        <div class="d-flex justify-content-center gap-3">
            @foreach ($links as $field => $label)
                <a href="{{ $microsite->{$field} }}" target="_blank"
                   class="w-11 h-11 rounded-pill bg-body border text-brand d-flex align-items-center justify-content-center fs-xs fw-bold shadow-sm">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </section>
@endif
