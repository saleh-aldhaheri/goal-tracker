<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Goals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Goals']); ?>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold">Goals</h1>
        <a href="<?php echo e(route('goals.create')); ?>" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-sm">+ New goal</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-2 mb-6 text-sm">
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search goals..." class="rounded-lg border-slate-300 text-sm">
        <select name="status" class="rounded-lg border-slate-300 text-sm">
            <option value="">All statuses</option>
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($status->value); ?>" <?php if(request('status') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select name="type" class="rounded-lg border-slate-300 text-sm">
            <option value="">All types</option>
            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($type->value); ?>" <?php if(request('type') === $type->value): echo 'selected'; endif; ?>><?php echo e($type->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-sm">Filter</button>
    </form>

    <div class="grid gap-3 md:grid-cols-2">
        <?php $__empty_1 = true; $__currentLoopData = $goals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $goal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
            <p class="text-sm text-slate-500">No goals match your filters.</p>
        <?php endif; ?>
    </div>

    <div class="mt-6"><?php echo e($goals->links()); ?></div>
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
<?php /**PATH /home/saleh/Desktop/Systems/Systems/goal-tracker/resources/views/goals/index.blade.php ENDPATH**/ ?>