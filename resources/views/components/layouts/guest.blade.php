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
<body>
    <div class="ambient">
        <div class="sky"></div>
        <div class="stars" id="stars"></div>
        <div class="clouds">
            <div class="cloud c1"></div><div class="cloud c2"></div><div class="cloud c3"></div>
        </div>
        <div class="wind" id="wind"></div>
    </div>
    <div class="auth">
        <div class="wrap" style="width:100%;max-width:400px">
            <div class="auth-box">
                <div class="brand">
                    <span class="mark"><svg viewBox="0 0 24 24" fill="none"><path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <div>
                        <h1>Goal Tracker</h1>
                        <div class="sub">Your progress, measured.</div>
                    </div>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
