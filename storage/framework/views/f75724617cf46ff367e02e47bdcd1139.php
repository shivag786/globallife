<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'help' => null,
    'as' => 'input',
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
    'type' => 'text',
    'value' => null,
    'help' => null,
    'as' => 'input',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<?php
    $errorKey = $name;
    $inputValue = old($errorKey, $value);
    $baseClasses = 'mt-[0.25rem] block w-full rounded-lg border-slate-300 shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-sm text-slate-800 '
        . 'transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 focus:outline-none '
        . 'placeholder:text-slate-400 disabled:bg-slate-100 disabled:text-slate-400';
    $errorClasses = $errors->has($errorKey) ? 'border-red-300 focus:border-red-500 focus:ring-red-500/30' : '';
?>

<div>
    <?php if($label): ?>
        <label for="<?php echo e($name); ?>" class="block text-sm font-medium text-slate-700"><?php echo e($label); ?></label>
    <?php endif; ?>

    <?php if($as === 'textarea'): ?>
        <textarea id="<?php echo e($name); ?>" name="<?php echo e($name); ?>" rows="4"
                  <?php echo e($attributes->merge(['class' => "$baseClasses $errorClasses"])); ?>><?php echo e($inputValue); ?></textarea>
    <?php elseif($as === 'select'): ?>
        <select id="<?php echo e($name); ?>" name="<?php echo e($name); ?>" <?php echo e($attributes->merge(['class' => "$baseClasses $errorClasses"])); ?>>
            <?php echo e($slot); ?>

        </select>
    <?php else: ?>
        <input id="<?php echo e($name); ?>" type="<?php echo e($type); ?>" name="<?php echo e($name); ?>" value="<?php echo e($inputValue); ?>"
               <?php echo e($attributes->merge(['class' => "$baseClasses $errorClasses"])); ?>>
    <?php endif; ?>

    <?php if($help): ?>
        <p class="mt-[0.25rem] text-xs text-slate-400"><?php echo e($help); ?></p>
    <?php endif; ?>

    <?php $__errorArgs = [$errorKey];
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
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/components/forms/input.blade.php ENDPATH**/ ?>