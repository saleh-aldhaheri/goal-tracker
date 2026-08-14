<x-layouts.app :title="$goal->name">
    <div class="flex items-start justify-between mb-6">
        <div>
            <p class="text-xs text-slate-500">{{ $goal->type->label() }} · {{ $goal->status->label() }}</p>
            <h1 class="text-xl font-semibold">{{ $goal->name }}</h1>
            @if ($goal->description)
                <p class="text-sm text-slate-600 mt-1">{{ $goal->description }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('goals.edit', $goal) }}" class="text-sm px-3 py-1.5 rounded-lg border border-slate-300">Edit</a>
            @if ($goal->status->value === 'active')
                <form method="POST" action="{{ route('goals.pause', $goal) }}">
                    @csrf
                    <button class="text-sm px-3 py-1.5 rounded-lg border border-slate-300">Pause</button>
                </form>
            @elseif ($goal->status->value === 'paused')
                <form method="POST" action="{{ route('goals.resume', $goal) }}">
                    @csrf
                    <button class="text-sm px-3 py-1.5 rounded-lg border border-slate-300">Resume</button>
                </form>
            @endif
            @if ($goal->status->value !== 'completed')
                <form method="POST" action="{{ route('goals.complete', $goal) }}">
                    @csrf
                    <button class="text-sm px-3 py-1.5 rounded-lg border border-slate-300">Mark complete</button>
                </form>
            @endif
        </div>
    </div>

    @php $dash = app(\App\Services\DashboardService::class)->goalDashboard($goal); @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Progress</p>
            <p class="text-2xl font-semibold">{{ $dash['progress'] }}%</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Time spent</p>
            <p class="text-2xl font-semibold">{{ intdiv($dash['time_spent_minutes'], 60) }}h {{ $dash['time_spent_minutes'] % 60 }}m</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Sessions</p>
            <p class="text-2xl font-semibold">{{ $dash['sessions'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Streak</p>
            <p class="text-2xl font-semibold">{{ $dash['current_streak'] }}d</p>
        </div>
    </div>

    @if (isset($dash['questions_total']))
        <div class="mb-8 bg-white rounded-xl border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold">Question coverage</p>
                <p class="text-sm text-slate-600">{{ $dash['questions_completed'] }} / {{ $dash['questions_total'] }}</p>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-slate-900" style="width: {{ $dash['questions_total'] > 0 ? min(100, round($dash['questions_completed'] / $dash['questions_total'] * 100)) : 0 }}%"></div>
            </div>
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-8">
        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold">Topics ({{ $dash['topics_completed'] }}/{{ $dash['topics_total'] }})</h2>
            </div>
            <form method="POST" action="{{ route('goals.topics.store', $goal) }}" class="flex gap-2 mb-3">
                @csrf
                <input name="name" placeholder="Add topic" required class="flex-1 rounded-lg border-slate-300 text-sm">
                <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-sm">Add</button>
            </form>
            <ul class="space-y-1">
                @foreach ($goal->topics as $topic)
                    <li class="flex items-center justify-between bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <span class="{{ $topic->isCompleted() ? 'line-through text-slate-400' : '' }}">{{ $topic->name }}</span>
                        @unless ($topic->isCompleted())
                            <form method="POST" action="{{ route('goals.topics.complete', [$goal, $topic]) }}">
                                @csrf
                                <button class="text-xs text-emerald-600">Complete</button>
                            </form>
                        @endunless
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">
                <h2 class="text-sm font-semibold mb-2">Milestones ({{ $dash['milestones_completed'] }}/{{ $dash['milestones_total'] }})</h2>
                <form method="POST" action="{{ route('goals.milestones.store', $goal) }}" class="flex gap-2 mb-3">
                    @csrf
                    <input name="name" placeholder="Add milestone" required class="flex-1 rounded-lg border-slate-300 text-sm">
                    <button class="px-3 py-1.5 rounded-lg border border-slate-300 text-sm">Add</button>
                </form>
                <ul class="space-y-1">
                    @foreach ($goal->milestones as $milestone)
                        <li class="flex items-center justify-between bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm">
                            <span class="{{ $milestone->status === 'completed' ? 'line-through text-slate-400' : '' }}">{{ $milestone->name }}</span>
                            @unless ($milestone->status === 'completed')
                                <form method="POST" action="{{ route('goals.milestones.complete', [$goal, $milestone]) }}">
                                    @csrf
                                    <button class="text-xs text-emerald-600">Complete</button>
                                </form>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div>
            <h2 class="text-sm font-semibold mb-2">Log progress</h2>
            <form method="POST" action="{{ route('goals.activities.store', $goal) }}" class="space-y-2 bg-white border border-slate-200 rounded-lg p-4 mb-6">
                @csrf
                <select name="type" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach (\App\Enums\ActivityType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="duration_minutes" placeholder="Minutes" class="rounded-lg border-slate-300 text-sm">
                    <input type="number" name="value" placeholder="Count (e.g. questions)" step="1" min="0" class="rounded-lg border-slate-300 text-sm">
                </div>
                <select name="topic_id" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">No topic</option>
                    @foreach ($goal->topics as $topic)
                        <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                    @endforeach
                </select>
                <textarea name="description" placeholder="Notes" class="w-full rounded-lg border-slate-300 text-sm"></textarea>
                <button class="w-full px-3 py-2 rounded-lg bg-slate-900 text-white text-sm">Log progress</button>
            </form>

            <h2 class="text-sm font-semibold mb-2">Recent activity</h2>
            <ul class="space-y-2">
                @forelse ($goal->activities as $activity)
                    <li class="bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span>{{ \App\Enums\ActivityType::tryFrom($activity->type)?->label() ?? $activity->type }}</span>
                            <span class="text-xs text-slate-500">{{ $activity->occurred_at->format('d M') }}</span>
                        </div>
                        @if ($activity->duration_minutes)
                            <p class="text-xs text-slate-500">{{ $activity->duration_minutes }} min</p>
                        @endif
                        @if ($activity->description)
                            <p class="text-xs text-slate-600 mt-1">{{ $activity->description }}</p>
                        @endif
                    </li>
                @empty
                    <p class="text-sm text-slate-500">No activity logged yet.</p>
                @endforelse
            </ul>
        </div>
    </div>
</x-layouts.app>
