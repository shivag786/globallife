@if ($microsite->isModuleVisible('reviews'))
    <section id="reviews" class="msite-section bg-cream-dark/30">
        <div class="msite-container">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Testimonials</p>
                <h2 class="msite-heading text-3xl md:text-4xl">What Customers Say</h2>
            </div>

            @if ($microsite->reviews->isNotEmpty())
                <div class="relative max-w-3xl mx-auto mb-14 overflow-hidden" data-msite-review-carousel>
                    <div class="flex transition-transform duration-500 ease-out" data-msite-review-track>
                        @foreach ($microsite->reviews as $review)
                            <div class="w-full flex-shrink-0 px-2">
                                <div class="msite-card p-8 text-center">
                                    <div class="flex justify-center gap-0.5 mb-4">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <x-icon name="star" :filled="true" class="w-5 h-5 {{ $i <= $review->rating ? 'text-gold-500' : 'text-slate-200' }}" />
                                        @endfor
                                    </div>
                                    @if ($review->review_text)
                                        <p class="text-slate-600 text-lg italic mb-5">&ldquo;{{ $review->review_text }}&rdquo;</p>
                                    @endif
                                    <p class="font-heading font-bold text-brand-950">
                                        {{ $review->customer_name }}
                                        @if ($review->is_verified)
                                            <span class="inline-flex items-center gap-1 text-xs text-green-600 font-normal ms-1">
                                                <x-icon name="check-circle" class="w-3.5 h-3.5" /> Verified
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($microsite->reviews->count() > 1)
                        <div class="flex justify-center gap-2 mt-6" data-msite-review-dots>
                            @foreach ($microsite->reviews as $index => $review)
                                <button type="button" data-msite-review-dot="{{ $index }}" aria-label="Review {{ $index + 1 }}"
                                        class="w-2 h-2 rounded-full bg-brand-300 hover:bg-brand-500 transition"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="msite-card p-6 md:p-8 max-w-lg mx-auto reveal">
                <h3 class="font-heading font-bold text-brand-950 mb-4 text-lg">Leave a Review</h3>

                @if (session('status'))
                    <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('microsite.reviews.store', [$microsite->city->slug, $microsite->business_slug, $microsite->user_id.'-'.$microsite->secure_token.'-'.$microsite->user->created_by]) }}" class="space-y-3">
                    @csrf
                    <input type="text" name="customer_name" placeholder="Your Name" required
                           class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                    <select name="rating" required class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Rating&hellip;</option>
                        @foreach ([5, 4, 3, 2, 1] as $rating)
                            <option value="{{ $rating }}">{{ $rating }} Star{{ $rating > 1 ? 's' : '' }}</option>
                        @endforeach
                    </select>
                    <textarea name="review_text" placeholder="Your experience (optional)" rows="3"
                              class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    <button type="submit" class="msite-btn msite-btn-primary w-full">Submit Review</button>
                </form>
            </div>
        </div>
    </section>
@endif
