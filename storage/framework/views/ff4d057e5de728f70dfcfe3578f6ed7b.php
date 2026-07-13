<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Lead Detail','heading' => 'Lead Detail']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Lead Detail','heading' => 'Lead Detail']); ?>
    <div class="grid md:grid-cols-3 gap-6 max-w-4xl">
        <div class="md:col-span-2 bg-white rounded-lg shadow-sm border border-slate-100 p-6 space-y-4">
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide">Name</p>
                <p class="font-medium"><?php echo e($lead->name); ?></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Email</p>
                    <p><?php echo e($lead->email); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Phone</p>
                    <p><?php echo e($lead->phone ?? '—'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">City</p>
                    <p><?php echo e($lead->city ?? '—'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Source</p>
                    <p class="capitalize"><?php echo e(str_replace('_', ' ', $lead->source)); ?></p>
                </div>
                <?php if($lead->interestedPlan): ?>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Interested Plan</p>
                        <p><?php echo e($lead->interestedPlan->name); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if($lead->message): ?>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Message</p>
                    <p class="whitespace-pre-line"><?php echo e($lead->message); ?></p>
                </div>
            <?php endif; ?>
            <p class="text-xs text-slate-400">Submitted <?php echo e($lead->created_at->format('M j, Y g:i A')); ?></p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
            <form method="POST" action="<?php echo e(route('admin.leads.update', $lead)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <?php $__currentLoopData = ['new', 'contacted', 'converted', 'closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option); ?>" <?php if(old('status', $lead->status) === $option): echo 'selected'; endif; ?>><?php echo e(ucfirst($option)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label for="assigned_manager_id" class="block text-sm font-medium text-slate-700">Assigned Manager</label>
                    <select id="assigned_manager_id" name="assigned_manager_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Unassigned</option>
                        <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($manager->id); ?>" <?php if(old('assigned_manager_id', $lead->assigned_manager_id) == $manager->id): echo 'selected'; endif; ?>><?php echo e($manager->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
                    Save
                </button>
            </form>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('leads.delete')): ?>
                <form action="<?php echo e(route('admin.leads.destroy', $lead)); ?>" method="POST" class="mt-3" onsubmit="return confirm('Delete this lead?');">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="w-full text-red-600 text-sm hover:underline">Delete Lead</button>
                </form>
            <?php endif; ?>
        </div>
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/admin/leads/show.blade.php ENDPATH**/ ?>