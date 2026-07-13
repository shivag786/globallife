<?php $highlighted = in_array($plan->slug, ['platinum-vip', 'gold-vip'], true); ?>
<div class="relative border <?php echo e($highlighted ? 'border-brand-600 ring-2 ring-brand-600' : 'border-slate-200'); ?> bg-white rounded-2xl p-6 flex flex-col premium-shadow hover:-translate-y-1 transition">
    <?php if($highlighted): ?>
        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gold-500 text-white text-xs font-semibold px-3 py-1 rounded-full">Popular</span>
    <?php endif; ?>
    <h3 class="font-display text-xl font-bold text-brand-900 mb-1"><?php echo e($plan->name); ?></h3>
    <p class="text-3xl font-extrabold text-brand-800 mb-1">₹<?php echo e(number_format($plan->monthly_price, 0)); ?><span class="text-sm font-normal text-slate-400">/mo</span></p>
    <p class="text-sm text-slate-400 mb-4">or ₹<?php echo e(number_format($plan->yearly_price, 0)); ?>/yr &middot; ₹<?php echo e(number_format($plan->joining_price, 0)); ?> joining</p>

    <ul class="space-y-2 text-sm flex-1 mb-6">
        <?php $__currentLoopData = $plan->features ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="flex items-start gap-2">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'check-circle','class' => 'w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> <?php echo e($feature); ?>

            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>

    <a href="<?php echo e(route('contact', ['plan' => $plan->slug])); ?>" class="block text-center bg-brand-700 text-white py-2.5 rounded-full font-medium hover:bg-brand-800 transition">
        Enquire Now
    </a>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/plan-card.blade.php ENDPATH**/ ?>