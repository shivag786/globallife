<?php if($microsite->products->isNotEmpty()): ?>
    <section id="products" class="msite-section">
        <div class="msite-container">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Shop</p>
                <h2 class="msite-heading text-3xl md:text-4xl">Our Products</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php $__currentLoopData = $microsite->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="msite-card msite-card-img overflow-hidden reveal" style="transition-delay: <?php echo e(($index % 4) * 0.1); ?>s">
                        <div class="relative msite-card-img h-44">
                            <?php if($product->image_path): ?>
                                <img src="<?php echo e(asset('storage/'.$product->image_path)); ?>" loading="lazy" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-brand-100 flex items-center justify-center">
                                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'tag','class' => 'w-8 h-8 text-brand-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'tag','class' => 'w-8 h-8 text-brand-400']); ?>
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
                            <?php endif; ?>
                            <?php if($product->show_pricing && $product->discount_percent): ?>
                                <span class="absolute top-0 right-3 bg-gold-500 text-brand-950 text-[11px] font-bold px-2.5 py-1 rounded-b-md">
                                    -<?php echo e($product->discount_percent); ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <?php if($product->brand): ?>
                                <p class="text-[11px] font-semibold text-brand-500 uppercase tracking-wide"><?php echo e($product->brand); ?></p>
                            <?php endif; ?>
                            <p class="font-heading font-semibold text-brand-950 text-sm mt-0.5 line-clamp-2"><?php echo e($product->name); ?></p>
                            <?php if($product->show_pricing && ($product->offer_price || $product->mrp)): ?>
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span class="font-bold text-brand-700">₹<?php echo e($product->offer_price ?? $product->mrp); ?></span>
                                    <?php if($product->offer_price && $product->mrp && $product->mrp > $product->offer_price): ?>
                                        <span class="text-xs text-slate-400 line-through">₹<?php echo e($product->mrp); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/microsite/products.blade.php ENDPATH**/ ?>