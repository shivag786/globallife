<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['microsite', 'navSections' => [], 'title' => null, 'metaDescription' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['microsite', 'navSections' => [], 'title' => null, 'metaDescription' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $settings = app(\App\Services\SettingsService::class)->all();
    $siteName = $settings['site_title'] ?? config('app.name');
    $pageDescription = $metaDescription ?? $microsite->short_description ?? $microsite->description;
    $ogImageValue = $microsite->cover_banner_path ? asset('storage/'.$microsite->cover_banner_path) : (! empty($settings['og_image']) ? asset('storage/'.$settings['og_image']) : null);
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title); ?> · <?php echo e($siteName); ?></title>
    <meta name="description" content="<?php echo e($pageDescription); ?>">

    <meta property="og:type" content="business.business">
    <meta property="og:title" content="<?php echo e($title); ?>">
    <meta property="og:description" content="<?php echo e($pageDescription); ?>">
    <?php if($ogImageValue): ?>
        <meta property="og:image" content="<?php echo e($ogImageValue); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">

    <?php if(! empty($settings['favicon'])): ?>
        <link rel="icon" href="<?php echo e(asset('storage/'.$settings['favicon'])); ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap.css')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <?php if(! empty($settings['gtm_id'])): ?>
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(), event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0], j=d.createElement(s), dl=l!='dataLayer'?'&l='+l:'';
            j.async=true; j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl; f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','<?php echo e($settings['gtm_id']); ?>');
        </script>
    <?php endif; ?>
    <?php if(! empty($settings['ga_measurement_id'])): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo e($settings['ga_measurement_id']); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '<?php echo e($settings['ga_measurement_id']); ?>');
        </script>
    <?php endif; ?>
</head>
<body class="bg-cream text-slate-800 antialiased" id="msite-body">
    <?php if(! empty($settings['gtm_id'])): ?>
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo e($settings['gtm_id']); ?>"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
    <?php endif; ?>

    <header id="msite-nav" class="sticky top-0 z-40 msite-glass border-b border-slate-200/60 transition-shadow">
        <div class="msite-container flex items-center justify-between h-16">
            <a href="#top" class="flex items-center gap-2.5 min-w-0">
                <?php if($microsite->logo_path): ?>
                    <img src="<?php echo e(asset('storage/'.$microsite->logo_path)); ?>" alt="<?php echo e($microsite->business_name); ?>" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                <?php endif; ?>
                <span class="font-heading font-bold text-brand-950 truncate"><?php echo e($microsite->business_name); ?></span>
            </a>

            <nav class="hidden lg:flex items-center gap-7 text-sm font-medium text-slate-600">
                <?php $__currentLoopData = $navSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anchor => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="#<?php echo e($anchor); ?>" data-msite-nav-link="<?php echo e($anchor); ?>" class="msite-nav-link hover:text-brand-700"><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            <div class="flex items-center gap-3">
                <?php if($microsite->phone_number): ?>
                    <a href="<?php echo e(route('microsite.click', [$microsite, 'call'])); ?>"
                       class="hidden sm:inline-flex msite-btn msite-btn-primary !h-10 !px-5 !text-sm">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'phone','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'phone','class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Call Now
                    </a>
                <?php endif; ?>
                <button type="button" class="lg:hidden text-slate-600" data-msite-menu-open aria-label="Open menu">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'bars','class' => 'w-6 h-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bars','class' => 'w-6 h-6']); ?>
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
            </div>
        </div>

        <div id="msite-mobile-nav" class="hidden lg:hidden border-t border-slate-200/60 bg-white">
            <nav class="msite-container py-4 flex flex-col gap-1 text-sm font-medium text-slate-600">
                <?php $__currentLoopData = $navSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anchor => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="#<?php echo e($anchor); ?>" data-msite-nav-link="<?php echo e($anchor); ?>" class="px-2 py-2 rounded hover:bg-slate-50 hover:text-brand-700"><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
        </div>
    </header>

    <main id="top">
        <?php echo e($slot); ?>

    </main>

    <footer class="bg-brand-950 text-brand-200 py-8 text-center text-sm">
        <div class="msite-container">
            <p><?php echo e($microsite->business_name); ?> &middot; <?php echo e($microsite->city->name); ?></p>
            <p class="text-xs text-brand-400 mt-2">
                Powered by <a href="<?php echo e(url('/')); ?>" class="hover:text-white"><?php echo e($siteName); ?></a>
            </p>
        </div>
    </footer>

    <button type="button" id="msite-back-to-top" data-msite-back-to-top
            class="msite-float-btn is-hidden fixed bottom-24 right-6 z-40 w-12 h-12 rounded-full bg-brand-900 text-white flex items-center justify-center shadow-lg hover:bg-brand-800"
            aria-label="Back to top">
        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-up','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-up','class' => 'w-5 h-5']); ?>
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

    <?php echo $__env->make('partials.microsite.floating-buttons', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/components/layouts/microsite.blade.php ENDPATH**/ ?>