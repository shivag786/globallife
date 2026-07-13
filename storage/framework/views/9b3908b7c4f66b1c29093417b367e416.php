<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Commission Partner Dashboard','heading' => 'Commission Partner Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Commission Partner Dashboard','heading' => 'Commission Partner Dashboard']); ?>
    <div class="mb-6">
        <p class="text-slate-600">Welcome back, <strong><?php echo e($manager->name); ?></strong>.</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100 mb-6">
        <h2 class="font-semibold mb-3">Your Assigned Cities</h2>
        <?php $__empty_1 = true; $__currentLoopData = $manager->cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between border-t border-slate-100 py-2 first:border-t-0">
                <span><?php echo e($city->name); ?>, <?php echo e($city->state); ?></span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-slate-400 text-sm">No cities assigned yet. Contact your Branch Manager.</p>
        <?php endif; ?>
        <div class="border-t border-slate-100 pt-3 mt-3 text-sm text-slate-500">
            Commission: <strong class="text-brand-700"><?php echo e($manager->commission_percentage); ?>%</strong>
            <?php if($manager->creator): ?>
                &mdash; set by <?php echo e($manager->creator->name); ?>

            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100 text-slate-500">
        Lead management, VIP enquiries, sales/revenue tracking, discount management, and customer
        management for your assigned cities ship in Phase 2.
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/dashboards/commission-partner.blade.php ENDPATH**/ ?>