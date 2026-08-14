<x-layouts.guest title="Register">
    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="field">
            <label class="label" for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="input">
        </div>
        <div class="field">
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input">
        </div>
        <div class="field">
            <label class="label" for="password">Password</label>
            <input id="password" name="password" type="password" required class="input">
        </div>
        <div class="field">
            <label class="label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="input">
        </div>
        <button type="submit" class="btn primary" style="width:100%;justify-content:center">Register</button>
    </form>

    <div class="auth-hint">Already have an account? <a href="{{ route('login') }}" style="color:var(--brass)">Log in</a></div>
</x-layouts.guest>
