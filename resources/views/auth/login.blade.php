<x-layouts.guest title="Log in">
    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field">
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input">
        </div>
        <div class="field">
            <label class="label" for="password">Password</label>
            <input id="password" name="password" type="password" required class="input">
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--dim);margin-bottom:16px">
            <input type="checkbox" name="remember" class="input checkbox"> Remember me
        </label>
        <button type="submit" class="btn primary" style="width:100%;justify-content:center">Log in</button>
    </form>

    <div class="auth-hint">No account? <a href="{{ route('register') }}" style="color:var(--brass)">Register</a></div>
</x-layouts.guest>
