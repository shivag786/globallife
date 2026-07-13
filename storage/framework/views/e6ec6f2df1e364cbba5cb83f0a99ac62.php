<?php
    $statIcons = ['star', 'users', 'map-pin', 'check-circle', 'sparkles', 'rupee'];
?>
<div class="bg-brand-900 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <?php if($section->title): ?>
            <h2 class="font-display text-2xl font-bold text-center text-white mb-10 reveal"><?php echo e($section->title); ?></h2>
        <?php endif; ?>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-8 text-center">
            <?php $__currentLoopData = $section->items ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="reveal" style="transition-delay: <?php echo e($index * 0.1); ?>s">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => ''.e($statIcons[$index % count($statIcons)]).'','class' => 'w-6 h-6 mx-auto mb-2 text-gold-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($statIcons[$index % count($statIcons)]).'','class' => 'w-6 h-6 mx-auto mb-2 text-gold-400']); ?>
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
                    <p class="text-4xl font-extrabold text-gold-400" data-countup="<?php echo e($item['value'] ?? ''); ?>">0</p>
                    <p class="text-sm text-brand-200 mt-2"><?php echo e($item['label'] ?? ''); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/stats.blade.php ENDPATH**/ ?>