<?php
    $avatarPalette = ['bg-brand-100 text-brand-700', 'bg-gold-400/20 text-gold-600', 'bg-brand-700 text-white'];
?>
<div class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-center mb-12 reveal">
        <h2 class="font-display text-3xl font-bold text-brand-900 mb-2"><?php echo e($section->title); ?></h2>
        <?php if($section->subtitle): ?>
            <p class="text-slate-500"><?php echo e($section->subtitle); ?></p>
        <?php endif; ?>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php $__currentLoopData = $section->items ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="team-card bg-white border border-slate-100 rounded-2xl p-6 text-center premium-shadow">
                <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center text-2xl font-bold mb-4 <?php echo e($avatarPalette[$index % 3]); ?>">
                    <?php echo e(mb_substr($member['name'] ?? '', 0, 1)); ?>

                </div>
                <p class="font-display font-semibold text-brand-900"><?php echo e($member['name'] ?? ''); ?></p>
                <p class="text-sm text-gold-600 font-medium mt-1"><?php echo e($member['role'] ?? ''); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/team.blade.php ENDPATH**/ ?>