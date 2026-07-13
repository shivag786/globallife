<?php $plans = app(\App\Repositories\VipPlanRepository::class)->activeOrdered(); ?>
<?php if($plans->isNotEmpty()): ?>
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display text-3xl font-bold text-brand-900 mb-2"><?php echo e($section->title); ?></h2>
            <?php if($section->subtitle): ?>
                <p class="text-slate-500"><?php echo e($section->subtitle); ?></p>
            <?php endif; ?>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="reveal" style="transition-delay: <?php echo e($index * 0.1); ?>s">
                    <?php echo $__env->make('partials.plan-card', ['plan' => $plan], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/vip_plans.blade.php ENDPATH**/ ?>