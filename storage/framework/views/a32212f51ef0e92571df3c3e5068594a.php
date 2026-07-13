<div class="max-w-4xl mx-auto px-6 py-16">
    <div class="reveal bg-white rounded-2xl p-10 md:p-14 flex flex-col md:flex-row items-center gap-8 premium-shadow">
        <?php if($section->image_path): ?>
            <img src="<?php echo e(asset('storage/'.$section->image_path)); ?>" alt="<?php echo e($section->title); ?>" class="w-28 h-28 rounded-full object-cover premium-shadow flex-shrink-0">
        <?php else: ?>
            <div class="w-20 h-20 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center flex-shrink-0">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'chat-bubble','class' => 'w-9 h-9']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chat-bubble','class' => 'w-9 h-9']); ?>
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
        <div>
            <div class="font-display text-xl md:text-2xl text-brand-900 italic leading-relaxed [&_p]:m-0"><?php echo $section->content; ?></div>
            <p class="mt-4 font-semibold text-brand-800"><?php echo e($section->title); ?></p>
            <?php if($section->subtitle): ?>
                <p class="text-sm text-slate-500"><?php echo e($section->subtitle); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/founder_quote.blade.php ENDPATH**/ ?>