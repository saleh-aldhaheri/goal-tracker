<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Goal Tracker' }} — Goal Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    @auth
        <nav class="border-b border-slate-200 bg-white">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="font-semibold">Goal Tracker</a>
                    <a href="{{ route('dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900">Dashboard</a>
                    <a href="{{ route('goals.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Goals</a>
                    <a href="{{ route('settings.tokens') }}" class="text-sm text-slate-600 hover:text-slate-900">Settings</a>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-slate-600 hover:text-slate-900">Log out</button>
                </form>
            </div>
        </nav>
    @endauth

    <main class="max-w-5xl mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('status') }}</div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
