@php
    $links = collect([
        'facebook_url' => 'facebook', 'instagram_url' => 'instagram', 'youtube_url' => 'youtube',
        'linkedin_url' => 'linkedin', 'twitter_url' => 'x', 'telegram_url' => 'telegram', 'pinterest_url' => 'pinterest',
    ])->filter(fn ($network, $field) => $microsite->{$field});
@endphp
@if ($links->isNotEmpty())
    <section class="pb-20 text-center reveal">
        <p class="fs-sm text-secondary mb-4">{{ $microsite->layout()->heading('social_media')['title'] }}</p>
        <div class="d-flex flex-wrap justify-content-center gap-3 px-4">
            @foreach ($links as $field => $network)
                <a href="{{ $microsite->{$field} }}" target="_blank" rel="noopener"
                   class="social-chip" style="width: 2.75rem; height: 2.75rem"
                   aria-label="{{ ucfirst($network) }}" title="{{ ucfirst($network) }}">
                    <x-social-icon :name="$network" class="w-6 h-6" />
                </a>
            @endforeach
        </div>
    </section>
@endif
