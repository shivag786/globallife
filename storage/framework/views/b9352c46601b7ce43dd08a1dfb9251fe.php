<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'VIP Members','heading' => 'VIP Members']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'VIP Members','heading' => 'VIP Members']); ?>
    <div class="flex justify-end mb-4">
        <a href="<?php echo e(route('manager.vip-members.create')); ?>" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">
            + Add VIP Member
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Business</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Account</th>
                    <th class="px-4 py-3">Activation</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium"><?php echo e($member->name); ?></td>
                        <td class="px-4 py-3"><?php echo e($member->vipMicrosite->business_name ?? '—'); ?></td>
                        <td class="px-4 py-3"><?php echo e($member->vipMicrosite->city->name ?? '—'); ?></td>
                        <td class="px-4 py-3"><?php echo e($member->vipMicrosite->vipPlan->name ?? '—'); ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs <?php echo e($member->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'); ?>">
                                <?php echo e($member->status); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <?php if($member->vipMicrosite?->isActivated()): ?>
                                <span class="px-2 py-0.5 rounded text-xs bg-brand-50 text-brand-700" title="<?php echo e($member->vipMicrosite->activated_at); ?>">
                                    Activated <?php echo e($member->vipMicrosite->activated_at->format('d M Y')); ?>

                                </span>
                            <?php else: ?>
                                <form action="<?php echo e(route('manager.vip-members.activate', $member)); ?>" method="POST" class="inline"
                                      onsubmit="return confirm('Confirm payment received and activate this VIP Member? This records the commission split and cannot be undone.')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="bg-gold-500 text-brand-950 text-xs font-semibold px-3 py-1.5 rounded-full hover:bg-gold-400">
                                        Activate
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <?php if($member->vipMicrosite): ?>
                                <a href="<?php echo e($member->vipMicrosite->publicPath()); ?>" target="_blank" class="text-brand-700 hover:underline">View Page</a>
                            <?php endif; ?>
                            <a href="<?php echo e(route('manager.vip-members.edit', $member)); ?>" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="<?php echo e(route('manager.vip-members.toggle-status', $member)); ?>" method="POST" class="inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button type="submit" class="text-amber-600 hover:underline">
                                    <?php echo e($member->status === 'active' ? 'Suspend' : 'Reactivate'); ?>

                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">You haven't added any VIP Members yet.</td></tr>
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/manager/vip-members/index.blade.php ENDPATH**/ ?>