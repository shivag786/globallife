<?php
    $microsite = $user->vipMicrosite;
    $tiles = [
        ['label' => 'Total Visitors', 'value' => $stats['total_visitors'], 'icon' => 'eye'],
        ["label" => "Today's Visitors", 'value' => $stats['today_visitors'], 'icon' => 'sparkles'],
        ['label' => 'Total Leads', 'value' => $stats['total_leads'], 'icon' => 'inbox'],
        ['label' => 'WhatsApp Clicks', 'value' => $stats['whatsapp_clicks'], 'icon' => 'chat-bubble'],
        ['label' => 'Call Clicks', 'value' => $stats['call_clicks'], 'icon' => 'phone'],
        ['label' => 'Direction Clicks', 'value' => $stats['direction_clicks'], 'icon' => 'map-pin'],
        ['label' => 'Website Clicks', 'value' => $stats['website_clicks'], 'icon' => 'share'],
        ['label' => 'Booking Requests', 'value' => $stats['booking_requests'], 'icon' => 'calendar'],
        ['label' => 'Reviews', 'value' => $stats['review_count'], 'icon' => 'star'],
    ];
?>
<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'VIP Dashboard','heading' => 'VIP Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'VIP Dashboard','heading' => 'VIP Dashboard']); ?>
    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="text-slate-600">Welcome back, <strong><?php echo e($user->name); ?></strong>.</p>
            <?php if($microsite): ?>
                <p class="text-sm text-slate-400"><?php echo e($microsite->business_name); ?> &middot; <?php echo e($microsite->city->name); ?> &middot; <?php echo e($microsite->vipPlan->name); ?></p>
            <?php endif; ?>
        </div>
        <?php if($microsite): ?>
            <a href="<?php echo e(url($microsite->publicPath())); ?>" target="_blank"
               class="inline-flex items-center gap-2 bg-brand-700 text-white text-sm px-4 py-2.5 rounded-full font-medium hover:bg-brand-800 premium-shadow transition">
                View Your Page <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right','class' => 'w-4 h-4']); ?>
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
            </a>
        <?php endif; ?>
    </div>

    <?php if($microsite): ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <?php $__currentLoopData = $tiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-gradient-to-br from-white to-brand-50/40 rounded-2xl p-5 border border-slate-100 premium-shadow">
                    <div class="w-9 h-9 rounded-full bg-brand-700/10 flex items-center justify-center mb-3">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => ''.e($tile['icon']).'','class' => 'w-5 h-5 text-brand-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($tile['icon']).'','class' => 'w-5 h-5 text-brand-700']); ?>
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
                    <p class="text-2xl font-bold text-brand-900"><?php echo e($tile['value']); ?></p>
                    <p class="text-xs uppercase tracking-wide text-slate-400 mt-1"><?php echo e($tile['label']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="bg-gradient-to-br from-brand-900 to-brand-950 rounded-2xl p-5 text-white premium-shadow">
                <p class="text-xs uppercase tracking-wide text-brand-200 mb-2">Profile Completion</p>
                <p class="text-3xl font-extrabold text-gold-400"><?php echo e($stats['completion']); ?>%</p>
                <div class="w-full h-2 bg-white/10 rounded-full mt-3 overflow-hidden">
                    <div class="h-full bg-gold-400 rounded-full" style="width: <?php echo e($stats['completion']); ?>%"></div>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4 mb-8">
            <a href="<?php echo e(route('vip.profile.edit')); ?>" class="bg-white rounded-xl p-4 border border-slate-100 hover:border-brand-300 transition premium-shadow">
                <p class="font-semibold text-brand-900 text-sm">Edit Business Profile</p>
                <p class="text-xs text-slate-400 mt-1">Basic info, contact, hours, social</p>
            </a>
            <a href="<?php echo e(route('vip.modules.edit')); ?>" class="bg-white rounded-xl p-4 border border-slate-100 hover:border-brand-300 transition premium-shadow">
                <p class="font-semibold text-brand-900 text-sm">Manage Section Visibility</p>
                <p class="text-xs text-slate-400 mt-1">Turn page sections on/off</p>
            </a>
            <a href="<?php echo e(route('vip.leads.index')); ?>" class="bg-white rounded-xl p-4 border border-slate-100 hover:border-brand-300 transition premium-shadow">
                <p class="font-semibold text-brand-900 text-sm">View Leads</p>
                <p class="text-xs text-slate-400 mt-1"><?php echo e($stats['total_leads']); ?> total enquiries</p>
            </a>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100 text-slate-500">
        Bookings, Offers/Coupons, Team Members, Certifications, and a Download Center ship in Phase 2.
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/dashboards/vip-member.blade.php ENDPATH**/ ?>