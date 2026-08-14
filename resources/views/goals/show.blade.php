<x-layouts.app :title="$goal->name">
    @php
        $dash = app(\App\Services\DashboardService::class)->goalDashboard($goal);
        $daysLeft = $goal->target_date ? now()->startOfDay()->diffInDays($goal->target_date, false) : null;
        $sparkMax = max(1, max($dash['daily_minutes_30']));
    @endphp

    <div class="top">
        <div>
            <a href="{{ route('goals.index') }}" class="crumb">← ALL GOALS</a>
            <div style="margin-top:8px;display:flex;gap:8px;align-items:center">
                <span class="chip">{{ $goal->type->label() }}</span>
                <span class="chip">{{ $goal->status->label() }}</span>
            </div>
            <h1 style="margin-top:10px">{{ $goal->name }}</h1>
            <div class="sub">{{ $goal->description }}</div>
        </div>
        <div style="display:flex;gap:8px">
            <a class="btn small" href="{{ route('goals.edit', $goal) }}">Edit</a>
            @if ($goal->status->value === 'active')
                <form method="POST" action="{{ route('goals.pause', $goal) }}">@csrf<button class="btn small">Pause</button></form>
            @elseif ($goal->status->value === 'paused')
                <form method="POST" action="{{ route('goals.resume', $goal) }}">@csrf<button class="btn small">Resume</button></form>
            @endif
            @if ($goal->status->value !== 'completed')
                <form method="POST" action="{{ route('goals.complete', $goal) }}">@csrf<button class="btn small">Complete</button></form>
            @endif
        </div>
    </div>

    <div class="card" style="margin-bottom:22px">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px">
            <div style="font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:64px;letter-spacing:2px;line-height:.85">{{ $dash['progress'] }}<span style="font-size:32px;color:var(--dim)">%</span></div>
            <div class="meta">{{ $dash['topics_total'] }} topics · {{ $dash['topics_completed'] }} done{{ $goal->target_date ? ' · due '.$goal->target_date->format('d M Y') : '' }}</div>
        </div>
        <div class="tape"><div class="fill" data-w="{{ $dash['progress'] }}"></div></div>
        <div class="foot">
            <span>{{ $daysLeft !== null ? ($daysLeft > 0 ? $daysLeft.' days left' : ($daysLeft === 0 ? 'due today' : 'past due')) : 'no deadline' }}</span>
            <span>{{ intdiv($dash['time_spent_minutes'], 60) }}h {{ $dash['time_spent_minutes'] % 60 }}m · {{ $dash['sessions'] }} sessions</span>
            <span class="fstreak"><i></i>streak {{ $dash['current_streak'] }}d</span>
        </div>
    </div>

    <div class="statgrid">
        <div class="stat"><div class="l">Time spent</div><div class="n">{{ intdiv($dash['time_spent_minutes'], 60) }}<span class="unit">h {{ $dash['time_spent_minutes'] % 60 }}m</span></div></div>
        <div class="stat"><div class="l">Sessions</div><div class="n"><span class="counter" data-count="{{ $dash['sessions'] }}">0</span></div></div>
        <div class="stat"><div class="l">This week</div><div class="n">{{ intdiv($dash['time_this_week'], 60) }}<span class="unit">h {{ $dash['time_this_week'] % 60 }}m</span></div></div>
        <div class="stat"><div class="l">Streak</div><div class="n"><span class="counter" data-count="{{ $dash['current_streak'] }}" data-suffix="d">0</span></div><div class="d">longest {{ $dash['longest_streak'] }}d</div></div>
        @isset($dash['questions_total'])
        <div class="stat"><div class="l">Questions</div><div class="n">{{ $dash['questions_completed'] }}<span class="unit">/{{ $dash['questions_total'] }}</span></div><div class="d">reviewed externally</div></div>
        @endisset
    </div>

    <div class="cols2">
        <div>
            <div class="panel">
                <h2>Topics <span class="hint">{{ $dash['topics_completed'] }} / {{ $dash['topics_total'] }}</span></h2>
                <form method="POST" action="{{ route('goals.topics.store', $goal) }}" style="display:flex;gap:8px;margin-bottom:12px">
                    @csrf
                    <input type="text" name="name" required placeholder="Add topic…" class="input" style="flex:1">
                    <button class="btn small">Add</button>
                </form>
                <div class="list">
                    @forelse ($goal->topics as $topic)
                        <div class="row-item {{ $topic->isCompleted() ? 'done' : '' }}">
                            @if ($topic->isCompleted())
                                <span class="check done"></span>
                            @else
                                <form method="POST" action="{{ route('goals.topics.complete', [$goal, $topic]) }}">
                                    @csrf
                                    <button type="submit" class="check checkbtn" title="Mark complete"></button>
                                </form>
                            @endif
                            <div class="grow">
                                <div class="t">{{ $topic->name }}</div>
                                <div class="s">{{ $topic->isCompleted() ? 'COMPLETED' : 'PENDING' }}</div>
                            </div>
                            <form method="POST" action="{{ route('goals.topics.destroy', [$goal, $topic]) }}" data-confirm="Remove the topic '{{ $topic->name }}'?">
                                @csrf @method('DELETE')
                                <button class="btn small danger">✕</button>
                            </form>
                        </div>
                    @empty
                        <div style="color:var(--dim)">No topics yet. Add one above.</div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2>Milestones <span class="hint">{{ $dash['milestones_completed'] }} / {{ $dash['milestones_total'] }}</span></h2>
                <form method="POST" action="{{ route('goals.milestones.store', $goal) }}" style="display:flex;gap:8px;margin-bottom:12px">
                    @csrf
                    <input type="text" name="name" required placeholder="Add milestone…" class="input" style="flex:1">
                    <button class="btn small">Add</button>
                </form>
                <div class="list">
                    @forelse ($goal->milestones as $milestone)
                        <div class="row-item {{ $milestone->status === 'completed' ? 'done' : '' }}">
                            @if ($milestone->status === 'completed')
                                <span class="check done"></span>
                            @else
                                <form method="POST" action="{{ route('goals.milestones.complete', [$goal, $milestone]) }}">
                                    @csrf
                                    <button type="submit" class="check checkbtn" title="Mark complete"></button>
                                </form>
                            @endif
                            <div class="grow">
                                <div class="t">{{ $milestone->name }}</div>
                                <div class="s">{{ $milestone->status === 'completed' ? 'COMPLETED' : 'PENDING' }}</div>
                            </div>
                            <form method="POST" action="{{ route('goals.milestones.destroy', [$goal, $milestone]) }}" data-confirm="Remove the milestone '{{ $milestone->name }}'?">
                                @csrf @method('DELETE')
                                <button class="btn small danger">✕</button>
                            </form>
                        </div>
                    @empty
                        <div style="color:var(--dim)">No milestones yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            <div class="panel">
                <h2>Log progress</h2>
                <form method="POST" action="{{ route('goals.activities.store', $goal) }}" style="background:var(--panel);border:1px solid var(--line);padding:16px">
                    @csrf
                    <div class="field">
                        <label class="label">Activity</label>
                        <select name="type" class="select">
                            @foreach (\App\Enums\ActivityType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label class="label">Duration (min)</label>
                            <input type="number" name="duration_minutes" class="input" placeholder="e.g. 60">
                        </div>
                        <div class="field">
                            <label class="label">Topic</label>
                            <select name="topic_id" class="select">
                                <option value="">No topic</option>
                                @foreach ($goal->topics as $topic)
                                    <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Notes</label>
                        <textarea name="description" class="textarea" placeholder="What did you work on?"></textarea>
                    </div>
                    <button type="submit" class="btn primary" style="width:100%;justify-content:center">Save entry</button>
                </form>
            </div>

            <div class="panel">
                <h2>Recent activity</h2>
                <div class="timeline">
                    @forelse ($goal->activities as $activity)
                        <div class="tl">
                            <div class="d">{{ $activity->occurred_at->format('d M · H:i') }}</div>
                            <div class="t">{{ \App\Enums\ActivityType::tryFrom($activity->type)?->label() ?? str_replace('_', ' ', $activity->type) }}{{ $activity->duration_minutes ? ' · '.$activity->duration_minutes.'m' : '' }}</div>
                            @if ($activity->description)
                                <div class="s">{{ $activity->description }}</div>
                            @endif
                        </div>
                    @empty
                        <div style="color:var(--dim)">No activity logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <h2>30-day activity <span class="hint">minutes per day</span></h2>
        <div class="spark">
            @foreach ($dash['daily_minutes_30'] as $m)
                <i class="{{ $m === $sparkMax && $sparkMax > 1 ? 'hi' : '' }}" data-h="{{ (int) round($m / $sparkMax * 100) }}" style="height:0%"></i>
            @endforeach
        </div>
    </div>
</x-layouts.app>
