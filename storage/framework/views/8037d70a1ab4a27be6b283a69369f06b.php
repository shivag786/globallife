<?php $testimonials = app(\App\Repositories\TestimonialRepository::class)->activeOrdered(); ?>
<?php if($testimonials->isNotEmpty()): ?>
    <div id="testimonials" class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display text-3xl font-bold text-brand-900 mb-2"><?php echo e($section->title); ?></h2>
            <?php if($section->subtitle): ?>
                <p class="text-slate-500"><?php echo e($section->subtitle); ?></p>
            <?php endif; ?>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="reveal bg-white border border-slate-100 rounded-2xl p-6 premium-shadow" style="transition-delay: <?php echo e($index * 0.1); ?>s">
                    <div class="flex gap-0.5 mb-3">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'star','filled' => true,'class' => 'w-4 h-4 '.e($i <= $testimonial->rating ? 'text-gold-500' : 'text-slate-200').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','filled' => true,'class' => 'w-4 h-4 '.e($i <= $testimonial->rating ? 'text-gold-500' : 'text-slate-200').'']); ?>
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
                    <p class="text-slate-600 text-sm mb-5">&ldquo;<?php echo e($testimonial->content); ?>&rdquo;</p>
                    <div class="flex items-center gap-3">
                        <?php if($testimonial->photo): ?>
                            <img src="<?php echo e(asset('storage/'.$testimonial->photo)); ?>" alt="<?php echo e($testimonial->name); ?>" class="w-10 h-10 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-sm font-semibold">
                                <?php echo e(mb_substr($testimonial->name, 0, 1)); ?>

                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="font-semibold text-brand-900 text-sm"><?php echo e($testimonial->name); ?></p>
                            <p class="text-xs text-slate-400"><?php echo e(collect([$testimonial->role_title, $testimonial->city])->filter()->implode(', ')); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/testimonials_showcase.blade.php ENDPATH**/ ?>