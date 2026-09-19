@if ($microsite->videos->isNotEmpty())
    <section id="videos" class="msite-section">
        <div class="msite-container">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Watch</p>
                <h2 class="msite-heading text-3xl md:text-4xl">Videos</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($microsite->videos as $video)
                    @php
                        $youtubeId = \App\Models\BusinessVideo::extractYoutubeId($video->youtube_url);

                        // Fallback: watch?v= / youtu.be / embed / shorts / live / v
                        if (! $youtubeId && preg_match(
                            '~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/))([A-Za-z0-9_-]{11})~',
                            (string) $video->youtube_url,
                            $ytMatch
                        )) {
                            $youtubeId = $ytMatch[1];
                        }
                    @endphp

                    {{-- ID nahi mila to skip karo, warna click par kuch nahi hoga --}}
                    @continue(! $youtubeId)

                    <button type="button" data-msite-video="{{ $youtubeId }}"
                            class="relative block rounded-xl overflow-hidden msite-card-img group reveal cursor-pointer">
                        <img src="{{ $video->thumbnail_url }}" loading="lazy" class="w-full h-44 object-cover">
                        <div class="absolute inset-0 bg-black/25 flex items-center justify-center group-hover:bg-black/35 transition">
                            <div class="w-14 h-14 rounded-full bg-white/95 flex items-center justify-center group-hover:scale-110 transition">
                                <x-icon name="play" :filled="true" class="w-6 h-6 text-brand-800 ml-0.5" />
                            </div>
                        </div>
                        @if ($video->title)
                            <p class="absolute bottom-2 left-3 right-3 text-left text-white text-sm font-medium drop-shadow truncate">{{ $video->title }}</p>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <div id="msite-video-modal" class="fixed inset-0 z-50 hidden bg-black/90 items-center justify-center p-4" data-msite-video-modal>
        <button type="button" data-msite-video-close aria-label="Close" class="absolute top-5 right-5 text-white/80 hover:text-white">
            <x-icon name="x-mark" class="w-8 h-8" />
        </button>
        <div class="w-full max-w-3xl aspect-video">
            <iframe data-msite-video-frame src="" class="w-full h-full rounded-lg"
                    allow="autoplay; encrypted-media; picture-in-picture"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen></iframe>
        </div>
    </div>

    {{-- Video modal behaviour (initMicrosite pe depend nahi karta) --}}
    <script>
        (function () {
            if (window.__msiteVideoBound) return;
            window.__msiteVideoBound = true;

            var modal = document.getElementById('msite-video-modal');
            if (!modal) return;
            var frame = modal.querySelector('[data-msite-video-frame]');

            function openModal(id) {
                frame.src = 'https://www.youtube.com/embed/' + encodeURIComponent(id) + '?autoplay=1&rel=0&playsinline=1';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                frame.src = '';
                document.body.style.overflow = '';
            }

            // Event delegation: DOM/reveal animations ke baad bhi kaam karega
            document.addEventListener('click', function (e) {
                var trigger = e.target.closest('[data-msite-video]');
                if (trigger) {
                    var id = trigger.getAttribute('data-msite-video');
                    if (id) openModal(id);
                    return;
                }

                if (e.target.closest('[data-msite-video-close]') || e.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });
        })();
    </script>
@endif