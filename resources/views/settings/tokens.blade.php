<x-layouts.app title="API & MCP tokens">
    <h1 class="text-lg font-semibold mb-6">API & MCP tokens</h1>

    @if (session('plainTextToken'))
        <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm">
            <p class="font-medium">Copy your token now — it won't be shown again:</p>
            <code class="block mt-2 break-all bg-white rounded px-2 py-1">{{ session('plainTextToken') }}</code>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.tokens.store') }}" class="space-y-3 bg-white border border-slate-200 rounded-xl p-4 mb-8 max-w-lg">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Token name</label>
            <input name="name" required placeholder="e.g. Claude MCP" class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Abilities</label>
            <div class="grid grid-cols-2 gap-2 text-sm">
                @foreach ($abilities as $ability)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="abilities[]" value="{{ $ability }}"> {{ $ability }}
                    </label>
                @endforeach
            </div>
        </div>
        <button class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-sm">Create token</button>
    </form>

    <h2 class="text-sm font-semibold mb-2">Active tokens</h2>
    <ul class="space-y-2">
        @forelse ($tokens as $token)
            <li class="flex items-center justify-between bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm">
                <div>
                    <p class="font-medium">{{ $token->name }}</p>
                    <p class="text-xs text-slate-500">{{ implode(', ', $token->abilities) }} · last used {{ $token->last_used_at?->diffForHumans() ?? 'never' }}</p>
                </div>
                <form method="POST" action="{{ route('settings.tokens.destroy', $token->id) }}" onsubmit="return confirm('Revoke this token?');">
                    @csrf
                    @method('DELETE')
                    <button class="text-xs text-red-600">Revoke</button>
                </form>
            </li>
        @empty
            <p class="text-sm text-slate-500">No tokens yet.</p>
        @endforelse
    </ul>
</x-layouts.app>
