<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Revenue Tracking','heading' => 'Revenue Tracking']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Revenue Tracking','heading' => 'Revenue Tracking']); ?>
    <div class="grid sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">VIP Members Activated</p>
            <p class="text-2xl font-bold text-brand-900"><?php echo e($totals['count']); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Total Earned</p>
            <p class="text-2xl font-bold text-brand-900">₹<?php echo e(number_format($totals['earned'], 2)); ?></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Date &amp; Time</th>
                    <th class="px-4 py-3">Business</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Package Amount</th>
                    <th class="px-4 py-3">Your %</th>
                    <th class="px-4 py-3">Your Earning</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 text-slate-500"><?php echo e($transaction->activated_at->format('d M Y, h:i A')); ?></td>
                        <td class="px-4 py-3 font-medium"><?php echo e($transaction->vipMicrosite->business_name ?? '—'); ?></td>
                        <td class="px-4 py-3"><?php echo e($transaction->vipMicrosite->city->name ?? '—'); ?></td>
                        <td class="px-4 py-3">₹<?php echo e(number_format($transaction->package_amount, 2)); ?></td>
                        <td class="px-4 py-3"><?php echo e($transaction->commission_partner_percentage); ?>%</td>
                        <td class="px-4 py-3 font-semibold text-brand-700">₹<?php echo e(number_format($transaction->commission_partner_amount, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No activations yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4"><?php echo e($transactions->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/manager/revenue/index.blade.php ENDPATH**/ ?>