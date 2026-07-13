<?php if($microsite->description || $microsite->short_description): ?>
    <?php
        $stats = array_filter([
            $microsite->establishment_year ? [now()->year - $microsite->establishment_year, 'Years Serving'] : null,
            $microsite->services->count() ? [$microsite->services->count(), 'Services'] : null,
            $microsite->products->count() ? [$microsite->products->count(), 'Products'] : null,
            $microsite->reviews->count() ? [$microsite->reviews->count(), 'Reviews'] : null,
        ]);
    ?>
    <section id="about" class="msite-section">
        <div class="msite-container grid lg:grid-cols-2 gap-14 items-center">
            <div class="reveal" data-reveal="left">
                <?php if($microsite->cover_banner_path): ?>
                    <img src="<?php echo e(asset('storage/'.$microsite->cover_banner_path)); ?>" alt="<?php echo e($microsite->business_name); ?>"
                         class="w-full h-80 lg:h-[26rem] object-cover rounded-2xl premium-shadow animate-float-y">
                <?php elseif($microsite->logo_path): ?>
                    <div class="flex items-center justify-center h-80 lg:h-[26rem] bg-brand-50 rounded-2xl">
                        <img src="<?php echo e(asset('storage/'.$microsite->logo_path)); ?>" alt="<?php echo e($microsite->business_name); ?>"
                             class="w-40 h-40 object-cover rounded-full premium-shadow animate-float-y">
                    </div>
                <?php endif; ?>
            </div>

            <div class="reveal" data-reveal="right">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">About Us</p>
                <h2 class="msite-heading text-3xl md:text-4xl mb-5">About <?php echo e($microsite->business_name); ?></h2>
                <?php if($microsite->short_description): ?>
                    <p class="text-lg text-slate-700 mb-4"><?php echo e($microsite->short_description); ?></p>
                <?php endif; ?>
                <?php if($microsite->description): ?>
                    <p class="text-slate-600 leading-[1.7] whitespace-pre-line mb-8"><?php echo e($microsite->description); ?></p>
                <?php endif; ?>

                <?php
                    $statsGridClass = match (count($stats)) {
                        1 => 'grid-cols-1',
                        2 => 'grid-cols-2',
                        3 => 'grid-cols-3',
                        default => 'grid-cols-4',
                    };
                ?>
                <?php if(!empty($stats)): ?>
                    <div class="grid <?php echo e($statsGridClass); ?> gap-6 border-t border-slate-100 pt-6">
                        <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$value, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <p class="text-2xl md:text-3xl font-extrabold text-brand-700"><?php echo e($value); ?>+</p>
                                <p class="text-xs text-slate-400 uppercase tracking-wide mt-1"><?php echo e($label); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/microsite/about.blade.php ENDPATH**/ ?>