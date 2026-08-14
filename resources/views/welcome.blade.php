<x-layouts.app title="Welcome">
    <div class="welcome">
        <h1>Goal Tracker</h1>
        <p>Track study revision, projects, habits, and recurring commitments — all derived from what you actually do.</p>
        <div style="margin-top:28px;display:flex;gap:10px;justify-content:center">
            @auth
                <a href="{{ route('dashboard') }}" class="btn primary">Go to dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn primary">Log in</a>
                <a href="{{ route('register') }}" class="btn">Register</a>
            @endauth
        </div>
    </div>
</x-layouts.app>
