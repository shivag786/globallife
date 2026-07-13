<div class="relative bg-gradient-to-b from-brand-100/60 via-cream to-cream overflow-hidden">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-200/30 rounded-full blur-3xl animate-float" aria-hidden="true"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-gold-400/20 rounded-full blur-3xl animate-float" style="animation-delay: -2s" aria-hidden="true"></div>
    <canvas id="hero-3d-canvas" class="absolute inset-0 -z-10 opacity-70" aria-hidden="true"></canvas>

    <div class="relative max-w-5xl mx-auto px-6 py-20 text-center">
        <?php if($section->image_path): ?>
            <img src="<?php echo e(asset('storage/'.$section->image_path)); ?>" alt="<?php echo e($section->title); ?>"
                 class="mx-auto mb-10 rounded-2xl premium-shadow max-h-96 object-cover animate-fade-in-up">
        <?php endif; ?>
        <h1 class="font-display text-4xl md:text-5xl font-bold text-brand-900 mb-5 leading-tight animate-fade-in-up"><?php echo e($section->title); ?></h1>
        <?php if($section->subtitle): ?>
            <p class="text-slate-600 text-lg max-w-2xl mx-auto mb-8 animate-fade-in-up" style="animation-delay: 0.1s"><?php echo e($section->subtitle); ?></p>
        <?php endif; ?>
        <?php if($section->cta_label && $section->cta_url): ?>
            <a href="<?php echo e($section->cta_url); ?>"
               class="inline-flex items-center gap-2 bg-brand-700 text-white px-8 py-3.5 rounded-full font-medium hover:bg-brand-800 hover:-translate-y-0.5 transition premium-shadow animate-fade-in-up"
               style="animation-delay: 0.2s">
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