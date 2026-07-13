@php
    $links = collect([
        'facebook_url' => 'FB', 'instagram_url' => 'IG', 'youtube_url' => 'YT',
        'linkedin_url' => 'IN', 'twitter_url' => 'X', 'telegram_url' => 'TG', 'pinterest_url' => 'PN',
    ])->filter(fn ($label, $field) => $microsite->{$field});
@endphp
@if ($links->isNotEmpty())
    <section class="pb-20 text-center reveal">
        <p class="text-sm text-slate-400 mb-4">Follow {{ $microsite->business_name }}</p>
        <div class="flex justify-center gap-3">
            @foreach ($links as $field => $label)
                <a href="{{ $microsite->{$field} }}" target="_blank"
                   class="w-11 h-11 rounded-full bg-white border border-slate-100 text-brand-700 flex items-center justify-center text-xs font-bold shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </section>
@endif
