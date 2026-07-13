<?php $plans = app(\App\Repositories\VipPlanRepository::class)->activeOrdered(); ?>
<div class="max-w-2xl mx-auto px-6 py-16">
    <div class="text-center mb-10 reveal">
        <h2 class="font-display text-3xl font-bold text-brand-900 mb-2"><?php echo e($section->title); ?></h2>
        <?php if($section->subtitle): ?>
            <p class="text-slate-500"><?php echo e($section->subtitle); ?></p>
        <?php endif; ?>
    </div>
    <div class="reveal bg-white border border-slate-100 rounded-2xl p-8 premium-shadow">
        <?php echo $__env->make('partials.enquiry-form', ['source' => 'homepage', 'plans' => $plans], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/enquiry_form.blade.php ENDPATH**/ ?>