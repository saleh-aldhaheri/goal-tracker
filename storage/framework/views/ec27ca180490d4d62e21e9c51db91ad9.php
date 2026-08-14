<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'New goal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'New goal']); ?>
    <h1 class="text-lg font-semibold mb-6">Create a goal</h1>

    <?php if($errors->any()): ?>
        <div class="mb-4 text-sm text-red-600">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('goals.store')); ?>" class="space-y-4 max-w-xl">
        <?php echo csrf_field(); ?>
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" value="<?php echo e(old('name')); ?>" required class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" class="w-full rounded-lg border-slate-300 text-sm"><?php echo e(old('description')); ?></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <select name="type" class="w-full rounded-lg border-slate-300 text-sm">
                    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->value); ?>"><?php echo e($type->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tracking mode</label>
                <select name="tracking_mode" class="w-full rounded-lg border-slate-300 text-sm">
                    <?php $__currentLoopData = $trackingModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($mode->value); ?>"><?php echo e($mode->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Priority</label>
                <select name="priority" class="w-full rounded-lg border-slate-300 text-sm">
                    <?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($priority->value); ?>"><?php echo e($priority->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Target date</label>
                <input type="date" name="target_date" value="<?php echo e(old('target_date')); ?>" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Target value <span class="text-slate-400">(optional — count/time goals)</span></label>
                <input type="number" step="0.01" name="target_value" value="<?php echo e(old('target_value')); ?>" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Target unit</label>
                <input name="target_unit" value="<?php echo e(old('target_unit')); ?>" placeholder="e.g. minutes, workouts" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium">Create goal</button>
    </form>
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
<?php /**PATH /home/saleh/Desktop/Systems/Systems/goal-tracker/resources/views/goals/create.blade.php ENDPATH**/ ?>