<x-layouts.app title="Dashboard">
    @php
        $weekMax = max(1, collect($summary['week_daily_minutes'])->max('minutes'));
        $goalMax = max(1, collect($summary['time_by_goal'])->max('minutes'));
    @endphp

    <div class="top">
        <div>
            <h1>The Homestead</h1>
            <div class="sub">{{ now()->format('l · j M Y') }}</div>
        </div>
        <button class="btn primary" onclick="openLog()">+ Log entry</button>
    </div>

    @php($xpPct = $game['level_needed'] > 0 ? min(100, (int) round($game['level_into'] / $game['level_needed'] * 100)) : 0)
    <div class="gamestrip">
        <div class="lvl"><span class="badge">LVL {{ $game['level'] }}</span> Gardener</div>
        <div class="xpwrap">
            <div class="row"><span>XP to level {{ $game['level'] + 1 }}</span><span>{{ number_format($game['level_into']) }} / {{ number_format($game['level_needed']) }} XP</span></div>
            <div class="xp"><div class="fill" style="width:{{ $xpPct }}%"></div></div>
        </div>
        <div class="chip">🪙 <span><b>{{ number_format($game['gold']) }}</b> gold</span></div>
        <div class="chip green">🔥 <span><b>{{ $game['streak'] }}d</b> streak</span></div>
    </div>

    <div class="hero">
        <div>
            <div class="k">Time logged today</div>
            <div class="big"><span class="counter" data-count="{{ $summary['time_today'] }}" data-fmt="time">0</span></div>
            <div class="cap">This week · {{ intdiv($summary['time_this_week'], 60) }}h {{ $summary['time_this_week'] % 60 }}m · {{ $summary['todays_activity'] }} logged today</div>
        </div>
        <div class="dial">
            <div class="ringwrap">
                <svg class="ring" viewBox="0 0 120 120">
                    <circle class="ring-bg" cx="60" cy="60" r="52"/>
                    <circle class="ring-fg" cx="60" cy="60" r="52" data-pct="{{ $summary['overall_progress'] }}"/>
                    <circle class="ff" cx="60" cy="2" r="3"/><circle class="ff" cx="101" cy="19" r="3"/><circle class="ff" cx="118" cy="60" r="3"/><circle class="ff" cx="101" cy="101" r="3"/><circle class="ff" cx="60" cy="118" r="3"/><circle class="ff" cx="19" cy="101" r="3"/><circle class="ff" cx="2" cy="60" r="3"/><circle class="ff" cx="19" cy="19" r="3"/>
                </svg>
                <div class="ringval"><span class="v"><span class="counter" data-count="{{ $summary['overall_progress'] }}" data-suffix="%">0</span></span></div>
            </div>
            <div class="l">overall progress</div>
        </div>
    </div>

    <div class="statgrid">
        <div class="stat">
            <div class="l">Active goals</div>
            <div class="n"><span class="counter" data-count="{{ $summary['total_active_goals'] }}">0</span></div>
            <div class="d">{{ $summary['goals_completed'] }} completed</div>
        </div>
        <div class="stat">
            <div class="l">Avg progress</div>
            <div class="n"><span class="counter" data-count="{{ $summary['overall_progress'] }}" data-suffix="%">0</span></div>
            <div class="d">across active goals</div>
        </div>
        <div class="stat">
            <div class="l">This week</div>
            <div class="n"><span class="counter" data-count="{{ $summary['time_this_week'] }}" data-fmt="time">0</span></div>
            <div class="d">{{ intdiv($summary['time_this_month'], 60) }}h {{ $summary['time_this_month'] % 60 }}m this month</div>
        </div>
        <div class="stat">
            <div class="l">Completed</div>
            <div class="n"><span class="counter" data-count="{{ $summary['goals_completed'] }}">0</span></div>
            <div class="d">goals done</div>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <h3>Time this week</h3>
            <div class="bars">
                @foreach ($summary['week_daily_minutes'] as $d)
                    <div class="bar">
                        <span class="v">{{ $d['minutes'] ? $d['minutes'].'m' : '' }}</span>
                        <div class="col {{ $d['minutes'] === $weekMax && $weekMax > 1 ? 'hi' : '' }}" data-h="{{ (int) round($d['minutes'] / $weekMax * 100) }}" style="height:0%"></div>
                        <span class="day">{{ $d['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="chart">
            <h3>Time by goal · this month</h3>
            <div class="hbars">
                @forelse ($summary['time_by_goal'] as $g)
                    <div class="hb">
                        <div class="lbl"><b>{{ $g['name'] }}</b><span>{{ $g['minutes'] ? intdiv($g['minutes'], 60).'h '.($g['minutes'] % 60).'m' : '0m' }}</span></div>
                        <div class="tape"><div class="fill" data-w="{{ (int) round($g['minutes'] / $goalMax * 100) }}"></div></div>
                    </div>
                @empty
                    <div style="color:var(--dim)">No time logged yet this month.</div>
                @endforelse
            </div>
        </div>
    </div>

    @if (count($summary['goals_needing_attention']))
        <div class="attn">
            <h2>Needs attention</h2>
            @foreach ($summary['goals_needing_attention'] as $goal)
                <div class="row">
                    <a href="{{ route('goals.show', $goal) }}">{{ $goal->name }}</a>
                    <span class="why">{{ $goal->target_date && $goal->target_date->isFuture() ? 'due '.$goal->target_date->format('d M') : 'no recent activity' }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="panel">
        <h2>Active goals <a class="btn small" href="{{ route('goals.index') }}">View all →</a></h2>
        <div class="goalgrid">
            @forelse ($summary['active_goals'] as $goal)
                <x-goal-card :goal="$goal" />
            @empty
                <div style="color:var(--dim)">No active goals yet. <a href="{{ route('goals.create') }}" style="color:var(--brass)">Create your first one</a>.</div>
            @endforelse
        </div>
    </div>

    @php($unlocked = count(array_filter($game['achievements'], fn ($a) => $a['unlocked'])))
    <div class="panel">
        <h2>Achievements <span class="hint">{{ $unlocked }} / {{ count($game['achievements']) }} unlocked</span></h2>
        <div class="achgrid">
            @foreach ($game['achievements'] as $a)
                <div class="achbadge {{ $a['unlocked'] ? '' : 'locked' }}">
                    <div class="ic">{{ $a['icon'] }}</div>
                    <div class="nm"><b>{{ $a['name'] }}</b><br>{{ $a['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
