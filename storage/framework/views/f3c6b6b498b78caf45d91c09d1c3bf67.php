<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <a href="<?php echo e(url('/')); ?>" class="text-2xl font-bold text-slate-800"><?php echo e(config('app.name')); ?></a>
        </div>
        <div class="bg-white shadow-md rounded-lg p-8">
            <?php echo e($slot); ?>

        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\global_life_new\resources\views/components/layouts/guest.blade.php ENDPATH**/ ?>