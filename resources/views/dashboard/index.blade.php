<x-layouts.app title="Dashboard">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Active goals</p>
            <p class="text-2xl font-semibold">{{ $summary['total_active_goals'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Overall progress</p>
            <p class="text-2xl font-semibold">{{ $summary['overall_progress'] }}%</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">This week</p>
            <p class="text-2xl font-semibold">{{ intdiv($summary['time_this_week'], 60) }}h {{ $summary['time_this_week'] % 60 }}m</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">This month</p>
            <p class="text-2xl font-semibold">{{ intdiv($summary['time_this_month'], 60) }}h {{ $summary['time_this_month'] % 60 }}m</p>
        </div>
    </div>

    @if (count($summary['goals_needing_attention']))
        <div class="mb-8">
            <h2 class="text-sm font-semibold text-slate-900 mb-2">Needs attention</h2>
            <div class="space-y-2">
                @foreach ($summary['goals_needing_attention'] as $goal)
                    <a href="{{ route('goals.show', $goal) }}" class="block bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 text-sm">
                        {{ $goal->name }} — {{ $goal->progress() }}% complete
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Active goals</h2>
        <a href="{{ route('goals.create') }}" class="text-sm text-slate-900 underline">+ New goal</a>
    </div>
    <div class="grid gap-3 md:grid-cols-2">
        @forelse ($summary['active_goals'] as $goal)
            <x-goal-card :goal="$goal" />
        @empty
            <p class="text-sm text-slate-500">No active goals yet. <a href="{{ route('goals.create') }}" class="underline">Create your first one</a>.</p>
        @endforelse
    </div>
</x-layouts.app>
