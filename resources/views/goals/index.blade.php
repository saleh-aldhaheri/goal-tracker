<x-layouts.app title="Goals">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold">Goals</h1>
        <a href="{{ route('goals.create') }}" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-sm">+ New goal</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-2 mb-6 text-sm">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search goals..." class="rounded-lg border-slate-300 text-sm">
        <select name="status" class="rounded-lg border-slate-300 text-sm">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="type" class="rounded-lg border-slate-300 text-sm">
            <option value="">All types</option>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-sm">Filter</button>
    </form>

    <div class="grid gap-3 md:grid-cols-2">
        @forelse ($goals as $goal)
            <x-goal-card :goal="$goal" />
        @empty
            <p class="text-sm text-slate-500">No goals match your filters.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $goals->links() }}</div>
</x-layouts.app>
