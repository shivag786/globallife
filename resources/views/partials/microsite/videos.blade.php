@if ($microsite->videos->isNotEmpty())
    <section id="videos" class="msite-section">
        <div class="msite-container">
            <div class="text-center mw-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">Watch</p>
                <h2 class="msite-heading fs-3xl fs-4xl">Videos</h2>
            </div>
            <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-6">
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
                            class="btn btn-link p-0 text-decoration-none position-relative d-block rounded-3 overflow-hidden msite-card-img reveal">
                        <img src="{{ $video->thumbnail_url }}" loading="lazy" class="w-100 h-44 object-fit-cover">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 d-flex align-items-center justify-content-center">
                            <div class="w-14 h-14 rounded-pill bg-white bg-opacity-100 d-flex align-items-center justify-content-center">
                                <x-icon name="play" :filled="true" class="w-6 h-6 text-brand-emphasis ms-1" />
                            </div>
                        </div>
                        @if ($video->title)
                            <p class="position-absolute bottom-2 start-3 end-3 text-start text-white fs-sm fw-medium text-shadow text-truncate">{{ $video->title }}</p>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <div id="msite-video-modal" class="position-fixed top-0 start-0 w-100 h-100 z-overlay d-none bg-black bg-opacity-100 align-items-center justify-content-center p-4" data-msite-video-modal>
        <button type="button" data-msite-video-close aria-label="Close" class="btn btn-link p-0 text-decoration-none position-absolute top-5 end-5 text-white text-opacity-75">
            <x-icon name="x-mark" class="w-8 h-8" />
        </button>
        <div class="w-100 mw-3xl aspect-video">
            <iframe data-msite-video-frame src="" class="w-100 h-100 rounded-2"
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
                modal.classList.remove('d-none');
                modal.classList.add('d-flex');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.add('d-none');
                modal.classList.remove('d-flex');
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
                if (e.key === 'Escape' && !modal.classList.contains('d-none')) closeModal();
            });
        })();
    </script>
@endif