<?php if($microsite->videos->isNotEmpty()): ?>
    <section id="videos" class="msite-section">
        <div class="msite-container">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Watch</p>
                <h2 class="msite-heading text-3xl md:text-4xl">Videos</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $microsite->videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $youtubeId = \App\Models\BusinessVideo::extractYoutubeId($video->youtube_url);
                    ?>
                    <button type="button" data-msite-video="<?php echo e($youtubeId); ?>"
                            class="relative block rounded-xl overflow-hidden msite-card-img group reveal">
                        <img src="<?php echo e($video->thumbnail_url); ?>" loading="lazy" class="w-full h-44 object-cover">
                        <div class="absolute inset-0 bg-black/25 flex items-center justify-center group-hover:bg-black/35 transition">
                            <div class="w-14 h-14 rounded-full bg-white/95 flex items-center justify-center group-hover:scale-110 transition">
                                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'play','filled' => true,'class' => 'w-6 h-6 text-brand-800 ml-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'play','filled' => true,'class' => 'w-6 h-6 text-brand-800 ml-0.5']); ?>
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
                        </div>
                        <?php if($video->title): ?>
                            <p class="absolute bottom-2 left-3 right-3 text-left text-white text-sm font-medium drop-shadow truncate"><?php echo e($video->title); ?></p>
                        <?php endif; ?>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>

    <div id="msite-video-modal" class="fixed inset-0 z-50 hidden bg-black/90 items-center justify-center p-4" data-msite-video-modal>
        <button type="button" data-msite-video-close aria-label="Close" class="absolute top-5 right-5 text-white/80 hover:text-white">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'x-mark','class' => 'w-8 h-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-8 h-8']); ?>
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
        </button>
        <div class="w-full max-w-3xl aspect-video">
            <iframe data-msite-video-frame src="" class="w-full h-full rounded-lg" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/microsite/videos.blade.php ENDPATH**/ ?>