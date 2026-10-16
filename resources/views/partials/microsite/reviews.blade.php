@if ($microsite->isModuleVisible('reviews'))
    <section id="reviews" class="msite-section bg-cream">
        <div class="msite-container">
            <div class="text-center mw-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">Testimonials</p>
                <h2 class="msite-heading fs-3xl fs-4xl">What Customers Say</h2>
            </div>

            @if ($microsite->reviews->isNotEmpty())
                <div class="position-relative mw-3xl mx-auto mb-14 overflow-hidden" data-msite-review-carousel>
                    <div class="d-flex" data-msite-review-track>
                        @foreach ($microsite->reviews as $review)
                            <div class="w-100 flex-shrink-0 px-2">
                                <div class="msite-card p-8 text-center">
                                    <div class="d-flex justify-content-center gap-1 mb-4">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <x-icon name="star" :filled="true" class="w-5 h-5 {{ $i <= $review->rating ? 'text-gold' : 'text-body-tertiary' }}" />
                                        @endfor
                                    </div>
                                    @if ($review->review_text)
                                        <p class="text-body-secondary fs-lg fst-italic mb-5">&ldquo;{{ $review->review_text }}&rdquo;</p>
                                    @endif
                                    <p class="font-heading fw-bold text-brand-emphasis">
                                        {{ $review->customer_name }}
                                        @if ($review->is_verified)
                                            <span class="d-inline-flex align-items-center gap-1 fs-xs text-success fw-normal ms-1">
                                                <x-icon name="check-circle" class="w-3.5 h-3.5" /> Verified
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($microsite->reviews->count() > 1)
                        <div class="d-flex justify-content-center gap-2 mt-6" data-msite-review-dots>
                            @foreach ($microsite->reviews as $index => $review)
                                <button type="button" data-msite-review-dot="{{ $index }}" aria-label="Review {{ $index + 1 }}"
                                        class="btn btn-link p-0 text-decoration-none w-2 h-2 rounded-pill bg-brand"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="msite-card p-6 p-md-8 mw-lg mx-auto reveal">
                <h3 class="font-heading fw-bold text-brand-emphasis mb-4 fs-lg">Leave a Review</h3>

                <form method="POST" action="{{ route('microsite.reviews.store', [$microsite->city->slug, $microsite->business_slug, $microsite->user_id.'-'.$microsite->secure_token.'-'.$microsite->user->created_by]) }}" class="d-flex flex-column gap-3">
                    @csrf
                    <input type="text" name="customer_name" placeholder="Your Name" required
                           class="d-block w-100 rounded-2 fs-sm">
                    <select name="rating" required class="d-block w-100 rounded-2 fs-sm">
                        <option value="">Rating&hellip;</option>
                        @foreach ([5, 4, 3, 2, 1] as $rating)
                            <option value="{{ $rating }}">{{ $rating }} Star{{ $rating > 1 ? 's' : '' }}</option>
                        @endforeach
                    </select>
                    <textarea name="review_text" placeholder="Your experience (optional)" rows="3"
                              class="d-block w-100 rounded-2 fs-sm"></textarea>
                    <button type="submit" class="btn btn-link p-0 text-decoration-none msite-btn msite-btn-primary w-100">Submit Review</button>
                </form>
            </div>
        </div>
    </section>

    {{-- SweetAlert popup for review status --}}
    @if (session('status'))
        <script>
            (function () {
                var message = @json(session('status'));

                function showAlert() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thank You!',
                        text: message,
                        confirmButtonText: 'OK',
                        timer: 4000,
                        timerProgressBar: true
                    });
                }

                if (window.Swal) {
                    showAlert();
                } else {
                    var s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                    s.onload = showAlert;
                    document.head.appendChild(s);
                }
            })();
        </script>
    @endif
@endif