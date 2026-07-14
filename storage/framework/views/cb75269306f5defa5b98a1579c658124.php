<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Dashboard','heading' => 'Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','heading' => 'Dashboard']); ?>
    <div class="mb-6">
        <p class="text-slate-600">Welcome back, <strong><?php echo e($user->name); ?></strong>.</p>
        <p class="text-sm text-slate-400">Role: <?php echo e($user->getRoleNames()->implode(', ')); ?></p>
    </div>

    <?php if($stats): ?>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <?php $__currentLoopData = [
                ['Cities', $stats['cities']],
                ['Branch Managers', $stats['branch_managers']],
                ['Commission Partners', $stats['commission_partners']],
                ['Active VIP Plans', $stats['vip_plans']],
                ['VIP Members', $stats['vip_members']],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs uppercase tracking-wide text-slate-400"><?php echo e($label); ?></p>
                    <p class="text-2xl font-bold text-slate-800" data-countup="<?php echo e($value); ?>">0</p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php if($charts): ?>
        <div class="grid lg:grid-cols-3 gap-4 mb-4">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5 border border-slate-100">
                <h2 class="font-semibold text-slate-800 mb-1">Company Revenue</h2>
                <p class="text-xs text-slate-400 mb-3">Company share of activations, last 6 months</p>
                <?php if (isset($component)) { $__componentOriginal257180076f2292f9911cfdbdab75fb25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal257180076f2292f9911cfdbdab75fb25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.apex-chart','data' => ['id' => 'chart-admin-revenue','type' => 'area','height' => 300,'series' => [['name' => 'Company Revenue', 'data' => $charts['revenue']['data']]],'categories' => $charts['revenue']['categories'],'currency' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('apex-chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'chart-admin-revenue','type' => 'area','height' => 300,'series' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['name' => 'Company Revenue', 'data' => $charts['revenue']['data']]]),'categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($charts['revenue']['categories']),'currency' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal257180076f2292f9911cfdbdab75fb25)): ?>
<?php $attributes = $__attributesOriginal257180076f2292f9911cfdbdab75fb25; ?>
<?php unset($__attributesOriginal257180076f2292f9911cfdbdab75fb25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal257180076f2292f9911cfdbdab75fb25)): ?>
<?php $component = $__componentOriginal257180076f2292f9911cfdbdab75fb25; ?>
<?php unset($__componentOriginal257180076f2292f9911cfdbdab75fb25); ?>
<?php endif; ?>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
                <h2 class="font-semibold text-slate-800 mb-1">Commission Split</h2>
                <p class="text-xs text-slate-400 mb-3">All-time payout distribution</p>
                <?php if (isset($component)) { $__componentOriginal257180076f2292f9911cfdbdab75fb25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal257180076f2292f9911cfdbdab75fb25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.apex-chart','data' => ['id' => 'chart-admin-split','type' => 'donut','height' => 300,'series' => [$charts['split']['partners'], $charts['split']['managers'], $charts['split']['company']],'labels' => ['Partners', 'Managers', 'Company'],'colors' => ['#5fa97e', '#d4af37', '#2c704c'],'currency' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('apex-chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'chart-admin-split','type' => 'donut','height' => 300,'series' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([$charts['split']['partners'], $charts['split']['managers'], $charts['split']['company']]),'labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Partners', 'Managers', 'Company']),'colors' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['#5fa97e', '#d4af37', '#2c704c']),'currency' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal257180076f2292f9911cfdbdab75fb25)): ?>
<?php $attributes = $__attributesOriginal257180076f2292f9911cfdbdab75fb25; ?>
<?php unset($__attributesOriginal257180076f2292f9911cfdbdab75fb25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal257180076f2292f9911cfdbdab75fb25)): ?>
<?php $component = $__componentOriginal257180076f2292f9911cfdbdab75fb25; ?>
<?php unset($__componentOriginal257180076f2292f9911cfdbdab75fb25); ?>
<?php endif; ?>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
                <h2 class="font-semibold text-slate-800 mb-1">New Users</h2>
                <p class="text-xs text-slate-400 mb-3">Sign-ups per month, last 6 months</p>
                <?php if (isset($component)) { $__componentOriginal257180076f2292f9911cfdbdab75fb25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal257180076f2292f9911cfdbdab75fb25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.apex-chart','data' => ['id' => 'chart-admin-users','type' => 'bar','height' => 280,'series' => [['name' => 'New Users', 'data' => $charts['users']['data']]],'categories' => $charts['users']['categories'],'colors' => ['#2c704c']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('apex-chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'chart-admin-users','type' => 'bar','height' => 280,'series' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['name' => 'New Users', 'data' => $charts['users']['data']]]),'categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($charts['users']['categories']),'colors' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['#2c704c'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal257180076f2292f9911cfdbdab75fb25)): ?>
<?php $attributes = $__attributesOriginal257180076f2292f9911cfdbdab75fb25; ?>
<?php unset($__attributesOriginal257180076f2292f9911cfdbdab75fb25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal257180076f2292f9911cfdbdab75fb25)): ?>
<?php $component = $__componentOriginal257180076f2292f9911cfdbdab75fb25; ?>
<?php unset($__componentOriginal257180076f2292f9911cfdbdab75fb25); ?>
<?php endif; ?>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
                <h2 class="font-semibold text-slate-800 mb-1">Leads</h2>
                <p class="text-xs text-slate-400 mb-3">Enquiries received per month, last 6 months</p>
                <?php if (isset($component)) { $__componentOriginal257180076f2292f9911cfdbdab75fb25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal257180076f2292f9911cfdbdab75fb25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.apex-chart','data' => ['id' => 'chart-admin-leads','type' => 'area','height' => 280,'series' => [['name' => 'Leads', 'data' => $charts['leads']['data']]],'categories' => $charts['leads']['categories'],'colors' => ['#d4af37']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('apex-chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'chart-admin-leads','type' => 'area','height' => 280,'series' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['name' => 'Leads', 'data' => $charts['leads']['data']]]),'categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($charts['leads']['categories']),'colors' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['#d4af37'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal257180076f2292f9911cfdbdab75fb25)): ?>
<?php $attributes = $__attributesOriginal257180076f2292f9911cfdbdab75fb25; ?>
<?php unset($__attributesOriginal257180076f2292f9911cfdbdab75fb25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal257180076f2292f9911cfdbdab75fb25)): ?>
<?php $component = $__componentOriginal257180076f2292f9911cfdbdab75fb25; ?>
<?php unset($__componentOriginal257180076f2292f9911cfdbdab75fb25); ?>
<?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($permissionMatrix): ?>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
            <h2 class="font-semibold mb-3">Your Permission Matrix</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400">
                        <th class="py-1">Module</th>
                        <th class="py-1">Actions Granted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $permissionMatrix; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $actions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t border-slate-100">
                            <td class="py-2 capitalize"><?php echo e($module); ?></td>
                            <td class="py-2">
                                <?php $__empty_1 = true; $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <span class="inline-block bg-indigo-50 text-indigo-700 text-xs px-2 py-0.5 rounded mr-1"><?php echo e($action); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="text-slate-300">none</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if (! ($stats || $permissionMatrix)): ?>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 text-slate-500">
            You don't have any modules assigned yet. Contact your Super Admin.
        </div>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/dashboards/admin.blade.php ENDPATH**/ ?>