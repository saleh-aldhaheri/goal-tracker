<x-layouts.app title="Welcome">
    <div class="max-w-2xl mx-auto text-center py-24">
        <h1 class="text-3xl font-semibold text-slate-900">Goal Tracker</h1>
        <p class="mt-3 text-slate-600">Track study revision, projects, habits, and recurring commitments in one place.</p>
        <div class="mt-8 flex justify-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium">Go to dashboard</a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium">Log in</a>
            @endauth
        </div>
    </div>
</x-layouts.app>
