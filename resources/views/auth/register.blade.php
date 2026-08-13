<x-layouts.guest title="Register">
    <div class="bg-white shadow-sm rounded-xl p-6 border border-slate-200">
        <h1 class="text-lg font-semibold mb-4">Create your account</h1>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1" for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                    class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="password">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <button type="submit" class="w-full rounded-lg bg-slate-900 text-white text-sm font-medium py-2">
                Register
            </button>
        </form>
        <p class="mt-4 text-sm text-slate-600">
            Already have an account? <a href="{{ route('login') }}" class="underline">Log in</a>
        </p>
    </div>
</x-layouts.guest>
