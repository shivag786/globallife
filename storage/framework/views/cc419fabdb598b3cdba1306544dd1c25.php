<?php
    $source = $source ?? 'contact_page';
    $plans = $plans ?? collect();
    $preselectedPlan = $preselectedPlan ?? null;
    $vipMicrositeId = $vipMicrositeId ?? null;
    $prefillCity = $prefillCity ?? null;
    $prefillMessage = $prefillMessage ?? null;
?>

<?php if(session('status')): ?>
    <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded p-3">
        <?php echo e(session('status')); ?>

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

<form method="POST" action="<?php echo e(route('enquiry.store')); ?>" class="space-y-4">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="source" value="<?php echo e($source); ?>">
    <?php if($vipMicrositeId): ?>
        <input type="hidden" name="vip_microsite_id" value="<?php echo e($vipMicrositeId); ?>">
    <?php endif; ?>
    <div class="hidden" aria-hidden="true">
        <label for="website-<?php echo e($source); ?>">Website</label>
        <input type="text" id="website-<?php echo e($source); ?>" name="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label for="name-<?php echo e($source); ?>" class="block text-sm font-medium text-slate-700">Name</label>
            <input id="name-<?php echo e($source); ?>" type="text" name="name" value="<?php echo e(old('name')); ?>" required
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="email-<?php echo e($source); ?>" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email-<?php echo e($source); ?>" type="email" name="email" value="<?php echo e(old('email')); ?>" required
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="phone-<?php echo e($source); ?>" class="block text-sm font-medium text-slate-700">Phone</label>
            <input id="phone-<?php echo e($source); ?>" type="text" name="phone" value="<?php echo e(old('phone')); ?>"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="city-<?php echo e($source); ?>" class="block text-sm font-medium text-slate-700">City</label>
            <input id="city-<?php echo e($source); ?>" type="text" name="city" value="<?php echo e(old('city', $prefillCity)); ?>"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
    </div>

    <?php if($plans->isNotEmpty()): ?>
        <div>
            <label for="interested_plan_id-<?php echo e($source); ?>" class="block text-sm font-medium text-slate-700">Interested Plan <span class="text-slate-400">(optional)</span></label>
            <select id="interested_plan_id-<?php echo e($source); ?>" name="interested_plan_id"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">None / General Enquiry</option>
                <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isSelected = old('interested_plan_id')
                            ? (string) old('interested_plan_id') === (string) $plan->id
                            : $preselectedPlan === $plan->slug;
                    ?>
                    <option value="<?php echo e($plan->id); ?>" <?php if($isSelected): echo 'selected'; endif; ?>><?php echo e($plan->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    <?php endif; ?>

    <div>
        <label for="message-<?php echo e($source); ?>" class="block text-sm font-medium text-slate-700">Message</label>
        <textarea id="message-<?php echo e($source); ?>" name="message" rows="4"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"><?php echo e(old('message', $prefillMessage)); ?></textarea>
    </div>

    <button type="submit" class="bg-brand-700 text-white px-6 py-3 rounded-full font-medium hover:bg-brand-800 transition">
        Send Enquiry
    </button>
</form>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/partials/enquiry-form.blade.php ENDPATH**/ ?>