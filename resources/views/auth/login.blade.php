<x-layouts.guest title="Log in">
    <div class="bg-white shadow-sm rounded-xl p-6 border border-slate-200">
        <h1 class="text-lg font-semibold mb-4">Log in</h1>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="password">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button type="submit" class="w-full rounded-lg bg-slate-900 text-white text-sm font-medium py-2">
                Log in
            </button>
        </form>
        <p class="mt-4 text-sm text-slate-600">
            No account? <a href="{{ route('register') }}" class="underline">Register</a>
        </p>
    </div>
</x-layouts.guest>
