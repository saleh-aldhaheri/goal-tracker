<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? 'Goal Tracker') }} — Goal Tracker</title>
    <script>
        (function () {
            try {
                var m = localStorage.getItem('gt-theme-mode') || 'system';
                var dark = (m === 'dark') || (m !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
            } catch (e) { document.documentElement.setAttribute('data-theme', 'dark'); }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="fadein" data-status="{{ session('status') }}">
    <div class="ambient">
        <div class="sky"></div>
        <div class="stars" id="stars"></div>
        <div class="clouds">
            <div class="cloud c1"></div><div class="cloud c2"></div><div class="cloud c3"></div>
        </div>
        <div class="wind" id="wind"></div>
    </div>
    @php
        $tickerItems = auth()->check()
            ? \App\Models\GoalActivity::with('goal')
                ->where('user_id', auth()->id())
                ->latest('occurred_at')->limit(8)->get()
            : collect();
        $quickGoals = auth()->check()
            ? auth()->user()->goals()->where('status', \App\Enums\GoalStatus::Active)->orderBy('name')->get()
            : collect();
    @endphp

    @auth
    <div class="tickerbar"><div class="inner">
        @forelse ($tickerItems as $a)
            <span class="tick"><b>{{ $a->occurred_at->format('H:i') }}</b> <span class="dot">●</span> {{ \Illuminate\Support\Str::upper($a->goal?->name) }} — {{ \Illuminate\Support\Str::upper(str_replace('_', ' ', $a->type)) }}{{ $a->duration_minutes ? ' '.$a->duration_minutes.'M' : '' }}</span>
        @empty
            <span class="tick"><b>--:--</b> <span class="dot">●</span> NO ACTIVITY YET — LOG YOUR FIRST ENTRY</span>
        @endforelse
    </div></div>
    @endauth

    <div class="shell">
        <aside class="side">
            <div class="brand"><b>Goal Tracker</b><span class="cap">unit 07</span></div>
            <nav class="nav">
                @auth
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'on' : '' }}"><span class="dot"></span>Dashboard</a>
                <a href="{{ route('goals.index') }}" class="{{ request()->routeIs('goals.*') ? 'on' : '' }}"><span class="dot"></span>Goals</a>
                <a href="{{ route('farm') }}" class="{{ request()->routeIs('farm') ? 'on' : '' }}"><span class="dot"></span>Farm</a>
                <a href="{{ route('settings.tokens') }}" class="{{ request()->routeIs('settings.*') ? 'on' : '' }}"><span class="dot"></span>Settings</a>
                @else
                <a href="{{ route('login') }}"><span class="dot"></span>Log in</a>
                @endauth
            </nav>
            <div class="themebox">
                <div class="lbl">Appearance</div>
                <div class="themeseg" id="themeSeg">
                    <button data-mode="light">Light</button>
                    <button data-mode="system" class="on">Auto</button>
                    <button data-mode="dark">Dark</button>
                </div>
            </div>
            <div class="side-foot">
                @auth
                <div class="r"><b>{{ \Illuminate\Support\Str::upper(auth()->user()->name) }}</b><span>OP-07</span></div>
                <div class="r"><span>{{ auth()->user()->email }}</span></div>
                <div class="r" style="margin-top:10px">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" style="background:none;border:none;color:var(--dim);font-family:'IBM Plex Mono',monospace;font-size:11px;cursor:pointer">LOG OUT →</button>
                    </form>
                </div>
                @else
                <div class="r"><b>GUEST</b><a href="{{ route('login') }}">LOG IN</a></div>
                @endauth
                <div class="clock" id="clock">--:--:--</div>
            </div>
        </aside>

        <main class="main">
            <div class="wrap">
                @if (session('status'))
                    <div class="flash">{{ session('status') }}</div>
                @endif
                {{ $slot }}
            </div>
        </main>

        <nav class="mobilebar">
            @auth
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'on' : '' }}">Dashboard</a>
            <a href="{{ route('goals.index') }}" class="{{ request()->routeIs('goals.*') ? 'on' : '' }}">Goals</a>
            <a href="{{ route('farm') }}" class="{{ request()->routeIs('farm') ? 'on' : '' }}">Farm</a>
            <a href="#" onclick="openLog();return false">Log</a>
            <a href="#" onclick="cycleTheme();return false" id="mTheme">Theme</a>
            @else
            <a href="{{ route('login') }}" class="on">Log in</a>
            <a href="#" onclick="cycleTheme();return false" id="mTheme">Theme</a>
            @endauth
        </nav>
    </div>

    <div class="toast" id="toast"></div>

    @auth
    <x-sound-control />
    @endauth

    <div class="modal-backdrop" id="confirmModal">
        <div class="modal" style="max-width:400px">
            <div style="font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:22px;letter-spacing:1px;text-transform:uppercase">Confirm</div>
            <p id="confirmMessage" style="color:var(--chalk);font-size:14px;margin:12px 0 22px;line-height:1.55"></p>
            <div style="display:flex;gap:8px;justify-content:flex-end">
                <button class="btn" id="confirmCancel">Cancel</button>
                <button class="btn red" id="confirmOk">Delete</button>
            </div>
        </div>
    </div>

    @auth
    <div class="modal-backdrop" id="quickLog">
        <div class="modal">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                <h2>Log entry</h2>
                <button class="btn small" onclick="closeLog()">✕</button>
            </div>
            <form method="POST" id="quickLogForm">
                @csrf
                <div class="field">
                    <label class="label">Goal</label>
                    <select name="goal" id="quickGoal" class="select" required>
                        @foreach ($quickGoals as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
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
                        <input type="number" name="duration_minutes" value="60" class="input" style="font-size:18px">
                    </div>
                    <div class="field">
                        <label class="label">Notes</label>
                        <input type="text" name="description" class="input" placeholder="Optional">
                    </div>
                </div>
                <button type="submit" class="btn primary" style="width:100%;justify-content:center">Save entry</button>
            </form>
        </div>
    </div>
    @endauth
</body>
</html>
