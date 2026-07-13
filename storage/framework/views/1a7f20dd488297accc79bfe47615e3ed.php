<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Section Visibility','heading' => 'Manage Section Visibility']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Section Visibility','heading' => 'Manage Section Visibility']); ?>
    <p class="text-sm text-slate-500 mb-6">Turn sections on or off for your public business page. Hidden sections stay saved &mdash; you can re-enable them anytime.</p>

    <form method="POST" action="<?php echo e(route('vip.modules.update')); ?>" class="max-w-2xl">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
            <h2 class="font-semibold mb-4">Page Sections</h2>
            <div class="space-y-3">
                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center justify-between border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                        <span class="text-sm text-slate-700"><?php echo e($label); ?></span>
                        <input type="checkbox" name="modules[<?php echo e($key); ?>]" value="1" class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500" <?php if($current[$key] ?? true): echo 'checked'; endif; ?>>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
            <h2 class="font-semibold mb-4">Floating Buttons</h2>
            <div class="space-y-3">
                <?php $__currentLoopData = $floatingButtons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center justify-between border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                        <span class="text-sm text-slate-700"><?php echo e($label); ?></span>
                        <input type="checkbox" name="modules[<?php echo e($key); ?>]" value="1" class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500" <?php if($current[$key] ?? true): echo 'checked'; endif; ?>>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <button type="submit" class="bg-brand-700 text-white text-sm px-5 py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
            Save Visibility
        </button>
    </form>
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/vip/modules/edit.blade.php ENDPATH**/ ?>