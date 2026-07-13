<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Branch Managers','heading' => 'Branch Managers']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Branch Managers','heading' => 'Branch Managers']); ?>
    <div class="flex justify-end mb-4">
        <a href="<?php echo e(route('admin.branch-managers.create')); ?>" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">
            + Add Branch Manager
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Branches (Cities)</th>
                    <th class="px-4 py-3">Commission Cap</th>
                    <th class="px-4 py-3">Commission Partners</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium"><?php echo e($manager->name); ?></td>
                        <td class="px-4 py-3"><?php echo e($manager->email); ?></td>
                        <td class="px-4 py-3"><?php echo e($manager->branchCities->pluck('name')->implode(', ')); ?></td>
                        <td class="px-4 py-3"><?php echo e($manager->commission_percentage); ?>%</td>
                        <td class="px-4 py-3"><?php echo e($manager->commission_partners_count); ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs <?php echo e($manager->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'); ?>">
                                <?php echo e($manager->status); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="<?php echo e(route('admin.branch-managers.permissions.edit', $manager)); ?>" class="text-indigo-600 hover:underline">Permissions</a>
                            <a href="<?php echo e(route('admin.branch-managers.edit', $manager)); ?>" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="<?php echo e(route('admin.branch-managers.toggle-status', $manager)); ?>" method="POST" class="inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button type="submit" class="text-amber-600 hover:underline">
                                    <?php echo e($manager->status === 'active' ? 'Suspend' : 'Activate'); ?>

                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">No branch managers yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/admin/branch-managers/index.blade.php ENDPATH**/ ?>