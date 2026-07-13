<div class="max-w-5xl mx-auto px-6 my-16">
    <div class="reveal py-16 px-8 text-center bg-gradient-to-br from-brand-700 to-brand-900 rounded-3xl text-white premium-shadow relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-48 h-48 bg-gold-400/10 rounded-full blur-2xl animate-float" aria-hidden="true"></div>
        <h2 class="font-display text-3xl font-bold mb-3"><?php echo e($section->title); ?></h2>
        <?php if($section->subtitle): ?>
            <p class="text-brand-100 mb-8"><?php echo e($section->subtitle); ?></p>
        <?php endif; ?>
        <?php if($section->cta_label && $section->cta_url): ?>
            <a href="<?php echo e($section->cta_url); ?>" class="inline-flex items-center gap-2 bg-gold-500 text-brand-950 px-8 py-3.5 rounded-full font-semibold hover:bg-gold-400 hover:-translate-y-0.5 transition">
                <?php echo e($section->cta_label); ?> <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/cta.blade.php ENDPATH**/ ?>