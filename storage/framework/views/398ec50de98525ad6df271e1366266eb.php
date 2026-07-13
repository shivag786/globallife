<div id="about" class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-12 items-center">
    <?php if($section->image_path): ?>
        <img src="<?php echo e(asset('storage/'.$section->image_path)); ?>" alt="<?php echo e($section->title); ?>" class="reveal rounded-2xl w-full object-cover premium-shadow">
    <?php endif; ?>
    <div class="reveal" style="transition-delay: 0.1s">
        <h2 class="font-display text-3xl font-bold text-brand-900 mb-5"><?php echo e($section->title); ?></h2>
        <?php if($section->content): ?>
            <div class="editor-content"><?php echo $section->content; ?></div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/home-sections/about.blade.php ENDPATH**/ ?>