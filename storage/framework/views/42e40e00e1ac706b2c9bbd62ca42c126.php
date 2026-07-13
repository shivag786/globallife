<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Homepage Banner','heading' => 'Homepage Banner']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Homepage Banner','heading' => 'Homepage Banner']); ?>
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
        <h2 class="font-semibold mb-3">Add Banner Slide</h2>
        <form method="POST" action="<?php echo e(route('vip.banners.store')); ?>" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-slate-700">Device</label>
                <select name="device" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
                    <option value="desktop">Desktop</option>
                    <option value="mobile">Mobile</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Image</label>
                <input type="file" name="image" accept="image/*" required class="mt-1 block text-sm text-slate-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Heading</label>
                <input type="text" name="heading" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Sub Heading</label>
                <input type="text" name="subheading" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Button Text</label>
                <input type="text" name="button_text" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Button Link</label>
                <input type="text" name="button_link" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">Add Slide</button>
            </div>
        </form>
    </div>

    <?php $__currentLoopData = ['Desktop' => $desktopBanners, 'Mobile' => $mobileBanners]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $banners): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <h2 class="font-semibold mb-3"><?php echo e($label); ?> Slides</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <?php $__empty_1 = true; $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
                    <img src="<?php echo e(asset('storage/'.$banner->image_path)); ?>" class="w-full h-32 object-cover">
                    <div class="p-3">
                        <p class="text-sm font-medium truncate"><?php echo e($banner->heading ?: 'Untitled'); ?></p>
                        <form method="POST" action="<?php echo e(route('vip.banners.update', $banner)); ?>" class="mt-2 flex items-center justify-between">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <label class="flex items-center gap-1.5 text-xs">
                                <input type="checkbox" name="is_visible" value="1" <?php if($banner->is_visible): echo 'checked'; endif; ?> onchange="this.form.submit()">
                                Visible
                            </label>
                        </form>
                        <form method="POST" action="<?php echo e(route('vip.banners.destroy', $banner)); ?>" onsubmit="return confirm('Remove this slide?')" class="mt-1">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:underline text-xs">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-400 text-sm col-span-full">No <?php echo e(strtolower($label)); ?> slides yet.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/vip/banners/index.blade.php ENDPATH**/ ?>