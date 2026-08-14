<x-layouts.app title="Change password">
    <div class="top">
        <div><h1>Change password</h1><div class="sub"><a href="{{ route('settings.tokens') }}" style="color:var(--brass)">← API &amp; MCP tokens</a></div></div>
    </div>

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.password.update') }}" style="max-width:440px">
        @csrf
        @method('PUT')
        <div class="field">
            <label class="label">Current password</label>
            <input type="password" name="current_password" required class="input">
        </div>
        <div class="field">
            <label class="label">New password</label>
            <input type="password" name="password" required class="input">
        </div>
        <div class="field">
            <label class="label">Confirm new password</label>
            <input type="password" name="password_confirmation" required class="input">
        </div>
        <button type="submit" class="btn primary">Update password</button>
    </form>
</x-layouts.app>
