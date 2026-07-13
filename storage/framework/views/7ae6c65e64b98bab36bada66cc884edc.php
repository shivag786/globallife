<?php $icons = ['heart', 'tag', 'users', 'sparkles']; ?>
<div class="bg-cream-dark/50 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="font-display text-3xl font-bold text-center text-brand-900 mb-12 reveal"><?php echo e($section->title); ?></h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php $__currentLoopData = $section->items ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="reveal bg-white border border-slate-100 rounded-2xl p-6 premium-shadow relative hover:-translate-y-1 transition"
                     style="transition-delay: <?php echo e($index * 0.1); ?>s">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-700 text-white text-sm font-bold"><?php echo e($index + 1); ?></span>
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $icons[$index % count($icons)],'class' => 'w-5 h-5 text-brand-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icons[$index % count($icons)]),'class' => 'w-5 h-5 text-brand-500']); ?>
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
                    <h3 class="font-display font-semibold text-brand-900 mb-2"><?php echo e($item['title'] ?? ''); ?></h3>
                    <p class="text-sm text-slate-500"><?php echo e($item['description'] ?? ''); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/process_steps.blade.php ENDPATH**/ ?>