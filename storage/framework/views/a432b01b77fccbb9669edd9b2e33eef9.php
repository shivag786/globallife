<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'current' => null,
    'help' => null,
]));

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

foreach (array_filter(([
    'name',
    'label' => null,
    'current' => null,
    'help' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div>
    <?php if($label): ?>
        <label for="<?php echo e($name); ?>" class="block text-sm font-medium text-slate-700"><?php echo e($label); ?></label>
    <?php endif; ?>

    <div class="mt-[0.25rem] flex items-center gap-[1rem]">
        <?php if($current): ?>
            <img src="<?php echo e(asset('storage/' . $current)); ?>" alt="<?php echo e($label); ?>"
                 class="h-14 w-14 rounded-lg border border-slate-200 object-contain bg-white p-[0.25rem]">
        <?php else: ?>
            <span class="flex h-14 w-14 items-center justify-center rounded-lg border border-dashed border-slate-300 text-[10px] text-slate-400">
                No file
            </span>
        <?php endif; ?>

        <input id="<?php echo e($name); ?>" type="file" name="<?php echo e($name); ?>" accept="image/*"
               <?php echo e($attributes->merge(['class' => 'block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100'])); ?>>
    </div>

    <?php if($help): ?>
        <p class="mt-[0.25rem] text-xs text-slate-400"><?php echo e($help); ?></p>
    <?php endif; ?>

    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-[0.25rem] text-xs text-red-600"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/components/forms/file-input.blade.php ENDPATH**/ ?>