<?php if($microsite->isModuleVisible('reviews')): ?>
    <section id="reviews" class="msite-section bg-cream-dark/30">
        <div class="msite-container">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Testimonials</p>
                <h2 class="msite-heading text-3xl md:text-4xl">What Customers Say</h2>
            </div>

            <?php if($microsite->reviews->isNotEmpty()): ?>
                <div class="relative max-w-3xl mx-auto mb-14 overflow-hidden" data-msite-review-carousel>
                    <div class="flex transition-transform duration-500 ease-out" data-msite-review-track>
                        <?php $__currentLoopData = $microsite->reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="w-full flex-shrink-0 px-2">
                                <div class="msite-card p-8 text-center">
                                    <div class="flex justify-center gap-0.5 mb-4">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'star','filled' => true,'class' => 'w-5 h-5 '.e($i <= $review->rating ? 'text-gold-500' : 'text-slate-200').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','filled' => true,'class' => 'w-5 h-5 '.e($i <= $review->rating ? 'text-gold-500' : 'text-slate-200').'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if($review->review_text): ?>
                                        <p class="text-slate-600 text-lg italic mb-5">&ldquo;<?php echo e($review->review_text); ?>&rdquo;</p>
                                    <?php endif; ?>
                                    <p class="font-heading font-bold text-brand-950">
                                        <?php echo e($review->customer_name); ?>

                                        <?php if($review->is_verified): ?>
                                            <span class="inline-flex items-center gap-1 text-xs text-green-600 font-normal ms-1">
                                                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'check-circle','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Verified
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <?php if($microsite->reviews->count() > 1): ?>
                        <div class="flex justify-center gap-2 mt-6" data-msite-review-dots>
                            <?php $__currentLoopData = $microsite->reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" data-msite-review-dot="<?php echo e($index); ?>" aria-label="Review <?php echo e($index + 1); ?>"
                                        class="w-2 h-2 rounded-full bg-brand-300 hover:bg-brand-500 transition"></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="msite-card p-6 md:p-8 max-w-lg mx-auto reveal">
                <h3 class="font-heading font-bold text-brand-950 mb-4 text-lg">Leave a Review</h3>

                <?php if(session('status')): ?>
                    <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3"><?php echo e(session('status')); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('microsite.reviews.store', [$microsite->city->slug, $microsite->business_slug, $microsite->user_id.'-'.$microsite->secure_token.'-'.$microsite->user->created_by])); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="customer_name" placeholder="Your Name" required
                           class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                    <select name="rating" required class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Rating&hellip;</option>
                        <?php $__currentLoopData = [5, 4, 3, 2, 1]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($rating); ?>"><?php echo e($rating); ?> Star<?php echo e($rating > 1 ? 's' : ''); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <textarea name="review_text" placeholder="Your experience (optional)" rows="3"
                              class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    <button type="submit" class="msite-btn msite-btn-primary w-full">Submit Review</button>
                </form>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/microsite/reviews.blade.php ENDPATH**/ ?>