<?php
    $heroSettings = app(\App\Services\SettingsService::class)->all();
?>
<div class="relative bg-gradient-to-b from-brand-100/60 via-cream to-cream overflow-hidden">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-200/30 rounded-full blur-3xl animate-float" aria-hidden="true"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-gold-400/20 rounded-full blur-3xl animate-float" style="animation-delay: -2s" aria-hidden="true"></div>
    <canvas id="hero-3d-canvas" class="absolute inset-0 -z-10 opacity-70" aria-hidden="true"></canvas>

    <div class="relative max-w-5xl mx-auto px-6 py-24 md:py-28 text-center">
        <?php if(! empty($heroSettings['site_tagline'])): ?>
            <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-brand-700 bg-white/70 backdrop-blur border border-brand-100 px-4 py-1.5 rounded-full mb-6 animate-fade-in-up">
                <span class="w-1.5 h-1.5 rounded-full bg-gold-500"></span><?php echo e($heroSettings['site_tagline']); ?>

            </span>
        <?php endif; ?>

        <?php if($section->image_path): ?>
            <img src="<?php echo e(asset('storage/'.$section->image_path)); ?>" alt="<?php echo e($section->title); ?>"
                 class="mx-auto mb-10 rounded-2xl premium-shadow max-h-96 object-cover animate-fade-in-up">
        <?php endif; ?>

        <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold text-brand-900 mb-5 leading-[1.1] animate-fade-in-up"><?php echo e($section->title); ?></h1>

        <?php if($section->subtitle): ?>
            <p class="text-slate-600 text-lg md:text-xl max-w-2xl mx-auto mb-9 leading-relaxed animate-fade-in-up" style="animation-delay: 0.1s"><?php echo e($section->subtitle); ?></p>
        <?php endif; ?>

        <div class="flex flex-wrap items-center justify-center gap-4 animate-fade-in-up" style="animation-delay: 0.2s">
            <?php if($section->cta_label && $section->cta_url): ?>
                <a href="<?php echo e($section->cta_url); ?>"
                   class="inline-flex items-center gap-2 bg-gradient-to-br from-brand-600 to-brand-800 text-white px-8 py-3.5 rounded-full font-medium hover:-translate-y-0.5 hover:brightness-105 transition premium-shadow">
                    <?php echo e($section->cta_label); ?>

                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right','class' => 'w-4 h-4']); ?>
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
                </a>
            <?php endif; ?>
            <a href="<?php echo e(route('contact')); ?>"
               class="inline-flex items-center gap-2 bg-white text-brand-800 border border-brand-200 px-8 py-3.5 rounded-full font-medium hover:border-brand-400 hover:-translate-y-0.5 transition">
                Talk to Us
            </a>
        </div>

        <?php if(! empty($heroSettings['hero_rating'])): ?>
            <div class="mt-8 inline-flex items-center gap-2.5 animate-fade-in-up" style="animation-delay: 0.3s">
                <div class="flex gap-0.5">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'star','filled' => true,'class' => 'w-4 h-4 text-gold-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','filled' => true,'class' => 'w-4 h-4 text-gold-500']); ?>
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
                <span class="text-sm text-slate-600">
                    <strong class="text-brand-900"><?php echo e($heroSettings['hero_rating']); ?></strong>
                    <?php if(! empty($heroSettings['hero_rating_count'])): ?>
                        &middot; <?php echo e($heroSettings['hero_rating_count']); ?> happy customers
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if($section->items): ?>
            <div class="flex flex-wrap justify-center gap-3 mt-10">
                <?php $__currentLoopData = $section->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="reveal inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 bg-white border border-brand-100 px-4 py-2 rounded-full premium-shadow"
                          style="transition-delay: <?php echo e($index * 0.08); ?>s">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'check-circle','class' => 'w-4 h-4 text-brand-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-4 h-4 text-brand-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> <?php echo e($badge['title'] ?? ''); ?>

                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/hero.blade.php ENDPATH**/ ?>