<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'API & MCP tokens']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'API & MCP tokens']); ?>
    <h1 class="text-lg font-semibold mb-6">API & MCP tokens</h1>

    <?php if(session('plainTextToken')): ?>
        <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm">
            <p class="font-medium">Copy your token now — it won't be shown again:</p>
            <code class="block mt-2 break-all bg-white rounded px-2 py-1"><?php echo e(session('plainTextToken')); ?></code>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('settings.tokens.store')); ?>" class="space-y-3 bg-white border border-slate-200 rounded-xl p-4 mb-8 max-w-lg">
        <?php echo csrf_field(); ?>
        <div>
            <label class="block text-sm font-medium mb-1">Token name</label>
            <input name="name" required placeholder="e.g. Claude MCP" class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Abilities</label>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <?php $__currentLoopData = $abilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ability): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="abilities[]" value="<?php echo e($ability); ?>"> <?php echo e($ability); ?>

                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <button class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-sm">Create token</button>
    </form>

    <h2 class="text-sm font-semibold mb-2">Active tokens</h2>
    <ul class="space-y-2">
        <?php $__empty_1 = true; $__currentLoopData = $tokens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $token): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="flex items-center justify-between bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm">
                <div>
                    <p class="font-medium"><?php echo e($token->name); ?></p>
                    <p class="text-xs text-slate-500"><?php echo e(implode(', ', $token->abilities)); ?> · last used <?php echo e($token->last_used_at?->diffForHumans() ?? 'never'); ?></p>
                </div>
                <form method="POST" action="<?php echo e(route('settings.tokens.destroy', $token->id)); ?>" onsubmit="return confirm('Revoke this token?');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button class="text-xs text-red-600">Revoke</button>
                </form>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-slate-500">No tokens yet.</p>
        <?php endif; ?>
    </ul>
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
<?php /**PATH /home/saleh/Desktop/Systems/Systems/goal-tracker/resources/views/settings/tokens.blade.php ENDPATH**/ ?>