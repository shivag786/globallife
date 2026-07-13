<?php
    $user = auth()->user();
    $adminSettings = app(\App\Services\SettingsService::class)->all();
    $adminSiteName = $adminSettings['site_title'] ?? config('app.name');
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? $adminSiteName); ?></title>
    <?php if(! empty($adminSettings['favicon'])): ?>
        <link rel="icon" href="<?php echo e(asset('storage/'.$adminSettings['favicon'])); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo e(asset('vendor/bootstrap.css')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-slate-50 text-slate-800">
    <div class="flex min-h-screen">
        <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-30 hidden md:hidden" data-sidebar-overlay></div>

        <aside id="admin-sidebar"
               class="w-64 bg-slate-900 text-slate-200 flex-shrink-0 fixed inset-y-0 left-0 z-40 -translate-x-full transition-transform duration-200 md:static md:translate-x-0 overflow-y-auto">
            <div class="px-6 py-5 text-lg font-bold text-white border-b border-slate-800 flex items-center gap-2 justify-between">
                <span class="flex items-center gap-2 min-w-0">
                    <?php if(! empty($adminSettings['site_logo'])): ?>
                        <img src="<?php echo e(asset('storage/'.$adminSettings['site_logo'])); ?>" alt="<?php echo e($adminSiteName); ?>" class="h-7 w-auto">
                    <?php endif; ?>
                    <span class="truncate"><?php echo e($adminSiteName); ?></span>
                </span>
                <button type="button" class="md:hidden text-slate-400 hover:text-white" data-sidebar-close aria-label="Close menu">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'x-mark','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-5 h-5']); ?>
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
            <nav class="px-3 py-4 space-y-1 text-sm">
                <?php if($user->hasAnyRole(['super_admin', 'admin', 'sub_admin'])): ?>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>

                    <?php if($user->hasRole('super_admin')): ?>
                        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Website</p>
                        <a href="<?php echo e(route('admin.home-sections.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Homepage Builder</a>

                        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Branch &amp; Commission Module</p>
                        <a href="<?php echo e(route('admin.cities.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Cities</a>
                        <a href="<?php echo e(route('admin.branch-managers.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Branch Managers</a>
                        <a href="<?php echo e(route('admin.commission-partners.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Commission Partners</a>

                        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">VIP Membership</p>
                        <a href="<?php echo e(route('admin.vip-plans.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">VIP Plans</a>

                        <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Platform</p>
                        <a href="<?php echo e(route('admin.revenue.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Revenue</a>
                        <a href="<?php echo e(route('admin.settings.edit')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Site Settings</a>
                        <a href="<?php echo e(route('admin.activity-logs.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Activity Log</a>
                    <?php endif; ?>

                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Content</p>
                    <?php if(\App\Services\PermissionMatrixService::userCanAccessModule($user, 'blog')): ?>
                        <a href="<?php echo e(route('admin.blog.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Blog</a>
                    <?php endif; ?>
                    <?php if(\App\Services\PermissionMatrixService::userCanAccessModule($user, 'products')): ?>
                        <a href="<?php echo e(route('admin.products.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Products</a>
                    <?php endif; ?>
                    <?php if(\App\Services\PermissionMatrixService::userCanAccessModule($user, 'testimonials')): ?>
                        <a href="<?php echo e(route('admin.testimonials.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Testimonials</a>
                    <?php endif; ?>
                    <?php if(\App\Services\PermissionMatrixService::userCanAccessModule($user, 'events')): ?>
                        <a href="<?php echo e(route('admin.events.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Events</a>
                    <?php endif; ?>
                    <?php if(\App\Services\PermissionMatrixService::userCanAccessModule($user, 'media')): ?>
                        <a href="<?php echo e(route('admin.media.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Media</a>
                    <?php endif; ?>
                    <?php if(\App\Services\PermissionMatrixService::userCanAccessModule($user, 'leads')): ?>
                        <a href="<?php echo e(route('admin.leads.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Leads</a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if($user->hasRole('branch_manager')): ?>
                    <a href="<?php echo e(route('branch.dashboard')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>
                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Branch Tools</p>
                    <?php $__currentLoopData = \App\Services\BranchPermissionMatrixService::MODULES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\App\Services\BranchPermissionMatrixService::userCanAccessModule($user, $module)): ?>
                            <?php if($module === 'commission-partners'): ?>
                                <a href="<?php echo e(route('branch.commission-partners.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Commission Partners</a>
                            <?php elseif($module === 'revenue-tracking'): ?>
                                <a href="<?php echo e(route('branch.revenue.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Revenue Tracking</a>
                            <?php else: ?>
                                <span class="flex items-center justify-between px-3 py-2 rounded text-slate-500">
                                    <?php echo e(ucwords(str_replace('-', ' ', $module))); ?>

                                    <span class="text-xs bg-slate-800 px-2 py-0.5 rounded">soon</span>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>

                <?php if($user->hasRole('commission_partner')): ?>
                    <a href="<?php echo e(route('manager.dashboard')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>
                    <a href="<?php echo e(route('manager.leads.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Leads</a>
                    <a href="<?php echo e(route('manager.vip-members.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">VIP Members</a>
                    <a href="<?php echo e(route('manager.revenue.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Revenue Tracking</a>
                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Partner Tools (Phase 2)</p>
                    <?php $__currentLoopData = ['Sales Tracking', 'Discount Management', 'Customer Management']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="flex items-center justify-between px-3 py-2 rounded text-slate-500">
                            <?php echo e($item); ?>

                            <span class="text-xs bg-slate-800 px-2 py-0.5 rounded">soon</span>
                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>

                <?php if($user->hasRole('vip_member')): ?>
                    <a href="<?php echo e(route('vip.dashboard')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>
                    <a href="<?php echo e(route('vip.profile.edit')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Profile</a>
                    <a href="<?php echo e(route('vip.modules.edit')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Section Visibility</a>

                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Business Page</p>
                    <a href="<?php echo e(route('vip.banners.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Homepage Banner</a>
                    <a href="<?php echo e(route('vip.services.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Services</a>
                    <a href="<?php echo e(route('vip.products.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Products</a>
                    <a href="<?php echo e(route('vip.gallery.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Gallery</a>
                    <a href="<?php echo e(route('vip.videos.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">YouTube Videos</a>
                    <a href="<?php echo e(route('vip.faqs.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">FAQs</a>

                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">Customers</p>
                    <a href="<?php echo e(route('vip.reviews.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Reviews</a>
                    <a href="<?php echo e(route('vip.leads.index')); ?>" class="block px-3 py-2 rounded hover:bg-slate-800">Leads</a>

                    <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-slate-500">VIP Tools (Phase 2)</p>
                    <?php $__currentLoopData = ['Offers & Coupons', 'Team Members', 'Bookings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="flex items-center justify-between px-3 py-2 rounded text-slate-500">
                            <?php echo e($item); ?>

                            <span class="text-xs bg-slate-800 px-2 py-0.5 rounded">soon</span>
                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" class="md:hidden text-slate-500 hover:text-slate-800 flex-shrink-0" data-sidebar-open aria-label="Open menu">
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
                    <h1 class="text-lg font-semibold truncate"><?php echo e($heading ?? 'Dashboard'); ?></h1>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 text-sm flex-shrink-0">
                    <span class="hidden sm:inline text-slate-500"><?php echo e($user->name); ?> &middot; <?php echo e($user->getRoleNames()->implode(', ')); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-red-600 hover:underline">Logout</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 overflow-x-hidden">
                <?php if(session('status')): ?>
                    <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded p-3">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-3">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-3">
                        <ul class="list-disc list-inside">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php echo e($slot); ?>

            </main>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>