<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['goal']));

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

foreach (array_filter((['goal']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a href="<?php echo e(route('goals.show', $goal)); ?>" class="block bg-white rounded-xl border border-slate-200 p-4 hover:border-slate-300">
    <div class="flex items-center justify-between">
        <p class="font-medium"><?php echo e($goal->name); ?></p>
        <span class="text-xs text-slate-500"><?php echo e($goal->type->label()); ?></span>
    </div>
    <div class="mt-3 h-2 bg-slate-100 rounded-full overflow-hidden">
        <div class="h-full bg-slate-900" style="width: <?php echo e($goal->progress()); ?>%"></div>
    </div>
    <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
        <span><?php echo e($goal->progress()); ?>% complete</span>
        <?php if($goal->target_date): ?>
            <span>Due <?php echo e($goal->target_date->format('d M Y')); ?></span>
        <?php endif; ?>
    </div>
</a>
<?php /**PATH /home/saleh/Desktop/Systems/Systems/goal-tracker/resources/views/components/goal-card.blade.php ENDPATH**/ ?>