@props(['goal'])

<a href="{{ route('goals.show', $goal) }}" class="block bg-white rounded-xl border border-slate-200 p-4 hover:border-slate-300">
    <div class="flex items-center justify-between">
        <p class="font-medium">{{ $goal->name }}</p>
        <span class="text-xs text-slate-500">{{ $goal->type->label() }}</span>
    </div>
    <div class="mt-3 h-2 bg-slate-100 rounded-full overflow-hidden">
        <div class="h-full bg-slate-900" style="width: {{ $goal->progress() }}%"></div>
    </div>
    <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
        <span>{{ $goal->progress() }}% complete</span>
        @if ($goal->target_date)
            <span>Due {{ $goal->target_date->format('d M Y') }}</span>
        @endif
    </div>
</a>
