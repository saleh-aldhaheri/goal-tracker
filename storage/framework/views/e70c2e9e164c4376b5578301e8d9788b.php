<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Goal Tracker'); ?> — Goal Tracker</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <?php if(auth()->guard()->check()): ?>
        <nav class="border-b border-slate-200 bg-white">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <a href="<?php echo e(route('dashboard')); ?>" class="font-semibold">Goal Tracker</a>
                    <a href="<?php echo e(route('dashboard')); ?>" class="text-sm text-slate-600 hover:text-slate-900">Dashboard</a>
                    <a href="<?php echo e(route('goals.index')); ?>" class="text-sm text-slate-600 hover:text-slate-900">Goals</a>
                </div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="text-sm text-slate-600 hover:text-slate-900">Log out</button>
                </form>
            </div>
        </nav>
    <?php endif; ?>

    <main class="max-w-5xl mx-auto px-4 py-8">
        <?php if(session('status')): ?>
            <div class="mb-6 rounded-lg bg-emerald-50 text-emerald-700 text-sm px-4 py-3"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php echo e($slot); ?>

    </main>
</body>
</html>
<?php /**PATH /home/saleh/Desktop/Systems/Systems/goal-tracker/resources/views/components/layouts/app.blade.php ENDPATH**/ ?>