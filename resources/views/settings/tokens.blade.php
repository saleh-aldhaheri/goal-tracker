<x-layouts.app title="API & MCP tokens">
    <div class="top">
        <div><h1>Settings</h1><div class="sub">API &amp; MCP access tokens · <a href="{{ route('settings.password') }}" style="color:var(--brass)">change password</a></div></div>
    </div>

    @if (session('plainTextToken'))
        <div class="tokenbox">
            Copy your token now — it won't be shown again:
            <code>{{ session('plainTextToken') }}</code>
        </div>
    @endif

    <div class="panel">
        <h2>Create token</h2>
        <form method="POST" action="{{ route('settings.tokens.store') }}" style="max-width:560px">
            @csrf
            <div class="field">
                <label class="label">Token name</label>
                <input name="name" required placeholder="e.g. Claude MCP" class="input">
            </div>
            <div class="field">
                <label class="label">Abilities</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                    @foreach ($abilities as $ability)
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--chalk)">
                            <input type="checkbox" name="abilities[]" value="{{ $ability }}" class="input checkbox"> {{ $ability }}
                        </label>
                    @endforeach
                </div>
            </div>
            <button class="btn primary">Create token</button>
        </form>
    </div>

    <div class="panel">
        <h2>Active tokens</h2>
        <div class="list">
            @forelse ($tokens as $token)
                <div class="row-item">
                    <div class="grow">
                        <div class="t">{{ $token->name }}</div>
                        <div class="s">{{ implode(', ', $token->abilities) }} · last used {{ $token->last_used_at?->diffForHumans() ?? 'never' }}</div>
                    </div>
                    <form method="POST" action="{{ route('settings.tokens.destroy', $token->id) }}" data-confirm="Revoke the token '{{ $token->name }}'?">
                        @csrf
                        @method('DELETE')
                        <button class="btn small danger">Revoke</button>
                    </form>
                </div>
            @empty
                <div style="color:var(--dim)">No tokens yet.</div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
