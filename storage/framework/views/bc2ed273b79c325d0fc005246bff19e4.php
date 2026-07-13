<?php
    $navSections = [];
    if ($microsite->isModuleVisible('about') && ($microsite->description || $microsite->short_description)) {
        $navSections['about'] = 'About';
    }
    if ($microsite->isModuleVisible('services') && $microsite->services->isNotEmpty()) {
        $navSections['services'] = 'Services';
    }
    if ($microsite->isModuleVisible('products') && $microsite->products->isNotEmpty()) {
        $navSections['products'] = 'Products';
    }
    if ($microsite->isModuleVisible('gallery') && $microsite->galleryItems->isNotEmpty()) {
        $navSections['gallery'] = 'Gallery';
    }
    if ($microsite->isModuleVisible('videos') && $microsite->videos->isNotEmpty()) {
        $navSections['videos'] = 'Videos';
    }
    if ($microsite->isModuleVisible('reviews')) {
        $navSections['reviews'] = 'Reviews';
    }
    if ($microsite->isModuleVisible('faqs') && $microsite->faqs->isNotEmpty()) {
        $navSections['faqs'] = 'FAQs';
    }
    if ($microsite->isModuleVisible('contact')) {
        $navSections['contact'] = 'Contact';
    }
?>
<?php if (isset($component)) { $__componentOriginal0cdbe1a3947ba2c16212bb77b5c8552d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0cdbe1a3947ba2c16212bb77b5c8552d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.microsite','data' => ['title' => $microsite->business_name,'microsite' => $microsite,'navSections' => $navSections]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.microsite'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($microsite->business_name),'microsite' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($microsite),'nav-sections' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($navSections)]); ?>
    <?php if($microsite->isModuleVisible('banner')): ?>
        <?php echo $__env->make('partials.microsite.banner', ['banners' => $microsite->banners], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php if($microsite->isModuleVisible('about')): ?>
        <?php echo $__env->make('partials.microsite.about', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php if($microsite->isModuleVisible('services')): ?>
        <?php echo $__env->make('partials.microsite.services', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php if($microsite->isModuleVisible('products')): ?>
        <?php echo $__env->make('partials.microsite.products', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php if($microsite->isModuleVisible('gallery')): ?>
        <?php echo $__env->make('partials.microsite.gallery', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php if($microsite->isModuleVisible('videos')): ?>
        <?php echo $__env->make('partials.microsite.videos', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php echo $__env->make('partials.microsite.reviews', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if($microsite->isModuleVisible('faqs')): ?>
        <?php echo $__env->make('partials.microsite.faqs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php if($microsite->isModuleVisible('contact')): ?>
        <?php echo $__env->make('partials.microsite.contact', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php if($microsite->isModuleVisible('social_media')): ?>
        <?php echo $__env->make('partials.microsite.social', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <section id="enquiry" class="msite-section pt-0">
        <div class="msite-container max-w-lg">
            <div class="msite-card p-8 reveal">
                <h2 class="msite-heading text-2xl mb-5">Get in Touch</h2>
                <?php echo $__env->make('partials.enquiry-form', [
                    'source' => 'microsite',
                    'vipMicrositeId' => $microsite->id,
                    'prefillCity' => $microsite->city->name,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0cdbe1a3947ba2c16212bb77b5c8552d)): ?>
<?php $attributes = $__attributesOriginal0cdbe1a3947ba2c16212bb77b5c8552d; ?>
<?php unset($__attributesOriginal0cdbe1a3947ba2c16212bb77b5c8552d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0cdbe1a3947ba2c16212bb77b5c8552d)): ?>
<?php $component = $__componentOriginal0cdbe1a3947ba2c16212bb77b5c8552d; ?>
<?php unset($__componentOriginal0cdbe1a3947ba2c16212bb77b5c8552d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/microsite/show.blade.php ENDPATH**/ ?>