<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'YouTube Videos','heading' => 'YouTube Videos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'YouTube Videos','heading' => 'YouTube Videos']); ?>
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
        <h2 class="font-semibold mb-3">Add Video (<?php echo e($videos->count()); ?>/6)</h2>
        <?php if($videos->count() < 6): ?>
            <form method="POST" action="<?php echo e(route('vip.videos.store')); ?>" class="flex flex-wrap items-end gap-3">
                <?php echo csrf_field(); ?>
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-sm font-medium text-slate-700">YouTube URL</label>
                    <input type="url" name="youtube_url" required placeholder="https://youtube.com/watch?v=..."
                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Title (optional)</label>
                    <input type="text" name="title" class="mt-1 rounded-md border-slate-300 shadow-sm text-sm">
                </div>
                <button type="submit" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">Add</button>
            </form>
        <?php else: ?>
            <p class="text-sm text-slate-400">You've reached the 6-video limit. Remove one to add another.</p>
        <?php endif; ?>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php $__empty_1 = true; $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
                <img src="<?php echo e($video->thumbnail_url); ?>" class="w-full h-36 object-cover">
                <div class="p-3">
                    <p class="text-sm font-medium truncate"><?php echo e($video->title ?: $video->youtube_url); ?></p>
                    <form method="POST" action="<?php echo e(route('vip.videos.destroy', $video)); ?>" onsubmit="return confirm('Remove this video?')" class="mt-2">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="text-red-600 hover:underline text-xs">Remove</button>
                    </form>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-slate-400 text-sm col-span-full">No videos yet.</p>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/vip/videos/index.blade.php ENDPATH**/ ?>