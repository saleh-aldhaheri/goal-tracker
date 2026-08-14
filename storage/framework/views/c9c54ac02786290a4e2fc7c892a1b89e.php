<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard']); ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Active goals</p>
            <p class="text-2xl font-semibold"><?php echo e($summary['total_active_goals']); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Overall progress</p>
            <p class="text-2xl font-semibold"><?php echo e($summary['overall_progress']); ?>%</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">This week</p>
            <p class="text-2xl font-semibold"><?php echo e(intdiv($summary['time_this_week'], 60)); ?>h <?php echo e($summary['time_this_week'] % 60); ?>m</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">This month</p>
            <p class="text-2xl font-semibold"><?php echo e(intdiv($summary['time_this_month'], 60)); ?>h <?php echo e($summary['time_this_month'] % 60); ?>m</p>
        </div>
    </div>

    <?php if(count($summary['goals_needing_attention'])): ?>
        <div class="mb-8">
            <h2 class="text-sm font-semibold text-slate-900 mb-2">Needs attention</h2>
            <div class="space-y-2">
                <?php $__currentLoopData = $summary['goals_needing_attention']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $goal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('goals.show', $goal)); ?>" class="block bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 text-sm">
                        <?php echo e($goal->name); ?> — <?php echo e($goal->progress()); ?>% complete
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Active goals</h2>
        <a href="<?php echo e(route('goals.create')); ?>" class="text-sm text-slate-900 underline">+ New goal</a>
    </div>
    <div class="grid gap-3 md:grid-cols-2">
        <?php $__empty_1 = true; $__currentLoopData = $summary['active_goals']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $goal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if (isset($component)) { $__componentOriginal54bd241290178a05e3fd7cec1b4f37b5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54bd241290178a05e3fd7cec1b4f37b5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.goal-card','data' => ['goal' => $goal]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('goal-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['goal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($goal)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal54bd241290178a05e3fd7cec1b4f37b5)): ?>
<?php $attributes = $__attributesOriginal54bd241290178a05e3fd7cec1b4f37b5; ?>
<?php unset($__attributesOriginal54bd241290178a05e3fd7cec1b4f37b5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal54bd241290178a05e3fd7cec1b4f37b5)): ?>
<?php $component = $__componentOriginal54bd241290178a05e3fd7cec1b4f37b5; ?>
<?php unset($__componentOriginal54bd241290178a05e3fd7cec1b4f37b5); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500">No active goals yet. <a href="<?php echo e(route('goals.create')); ?>" class="underline">Create your first one</a>.</p>
        <?php endif; ?>
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
<?php /**PATH /home/saleh/Desktop/Systems/Systems/goal-tracker/resources/views/dashboard/index.blade.php ENDPATH**/ ?>