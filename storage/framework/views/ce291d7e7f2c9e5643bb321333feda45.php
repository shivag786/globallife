<?php if($microsite->services->isNotEmpty()): ?>
    <section id="services" class="msite-section bg-cream-dark/30">
        <div class="msite-container">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">What We Offer</p>
                <h2 class="msite-heading text-3xl md:text-4xl">Our Services</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php $__currentLoopData = $microsite->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="msite-card msite-card-img overflow-hidden reveal" data-reveal="up" style="transition-delay: <?php echo e(($index % 3) * 0.1); ?>s">
                        <div class="relative msite-card-img h-52">
                            <?php if($service->image_path): ?>
                                <img src="<?php echo e(asset('storage/'.$service->image_path)); ?>" loading="lazy" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-brand-100 flex items-center justify-center">
                                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'sparkles','class' => 'w-10 h-10 text-brand-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sparkles','class' => 'w-10 h-10 text-brand-400']); ?>
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
                                </div>
                            <?php endif; ?>
                            <?php if($service->show_pricing && $service->discount_percent): ?>
                                <span class="absolute top-3 left-3 bg-gold-500 text-brand-950 text-xs font-bold px-3 py-1 rounded-full">
                                    <?php echo e($service->discount_percent); ?>% OFF
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <?php if($service->category): ?>
                                <p class="text-xs font-semibold text-brand-500 uppercase tracking-wide mb-1"><?php echo e($service->category); ?></p>
                            <?php endif; ?>
                            <p class="font-heading font-bold text-lg text-brand-950"><?php echo e($service->name); ?></p>
                            <?php if($service->short_description): ?>
                                <p class="text-sm text-slate-500 mt-2 line-clamp-2"><?php echo e($service->short_description); ?></p>
                            <?php endif; ?>

                            <div class="flex items-center justify-between mt-5">
                                <?php if($service->show_pricing && ($service->offer_price || $service->mrp)): ?>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl font-extrabold text-brand-700">₹<?php echo e($service->offer_price ?? $service->mrp); ?></span>
                                        <?php if($service->offer_price && $service->mrp && $service->mrp > $service->offer_price): ?>
                                            <span class="text-sm text-slate-400 line-through">₹<?php echo e($service->mrp); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>
                                <?php if($service->show_book_now): ?>
                                    <a href="<?php echo e(route('microsite.click', [$microsite, 'booking'])); ?>" class="msite-btn msite-btn-primary !h-10 !px-5 !text-sm">
                                        Book Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/microsite/services.blade.php ENDPATH**/ ?>