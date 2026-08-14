<x-layouts.app title="Change password">
    <h1 class="text-lg font-semibold mb-6">Change password</h1>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-600">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-4 max-w-sm bg-white border border-slate-200 rounded-xl p-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Current password</label>
            <input type="password" name="current_password" required class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">New password</label>
            <input type="password" name="password" required class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Confirm new password</label>
            <input type="password" name="password_confirmation" required class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium">Update password</button>
    </form>
</x-layouts.app>
