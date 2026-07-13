<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Dashboard','heading' => 'Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','heading' => 'Dashboard']); ?>
    <div class="mb-6">
        <p class="text-slate-600">Welcome back, <strong><?php echo e($user->name); ?></strong>.</p>
        <p class="text-sm text-slate-400">Role: <?php echo e($user->getRoleNames()->implode(', ')); ?></p>
    </div>

    <?php if($stats): ?>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">Cities</p>
                <p class="text-2xl font-bold"><?php echo e($stats['cities']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">Branch Managers</p>
                <p class="text-2xl font-bold"><?php echo e($stats['branch_managers']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">Commission Partners</p>
                <p class="text-2xl font-bold"><?php echo e($stats['commission_partners']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">Active VIP Plans</p>
                <p class="text-2xl font-bold"><?php echo e($stats['vip_plans']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">VIP Members</p>
                <p class="text-2xl font-bold"><?php echo e($stats['vip_members']); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if($permissionMatrix): ?>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <h2 class="font-semibold mb-3">Your Permission Matrix</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400">
                        <th class="py-1">Module</th>
                        <th class="py-1">Actions Granted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $permissionMatrix; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $actions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 capitalize"><?php echo e($module); ?></td>
                            <td class="py-2">
                                <?php $__empty_1 = true; $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <span class="inline-block bg-indigo-50 text-indigo-700 text-xs px-2 py-0.5 rounded mr-1"><?php echo e($action); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="text-slate-300">none</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if (! ($stats || $permissionMatrix)): ?>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100 text-slate-500">
            Content modules (blog, products, leads, events, media, testimonials) ship in Phase 2.
        </div>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/dashboards/admin.blade.php ENDPATH**/ ?>