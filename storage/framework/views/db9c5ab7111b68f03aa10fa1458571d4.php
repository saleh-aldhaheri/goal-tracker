<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Goal Tracker'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-slate-50 text-slate-900 antialiased flex min-h-screen items-center justify-center">
    <div class="w-full max-w-sm">
        <?php echo e($slot); ?>

    </div>
</body>
</html>
<?php /**PATH /home/saleh/Desktop/Systems/Systems/goal-tracker/resources/views/components/layouts/guest.blade.php ENDPATH**/ ?>