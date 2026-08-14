<x-layouts.app title="New goal">
    <div class="top">
        <div><h1>New goal</h1><div class="sub">Define a commitment and how to measure it</div></div>
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

    <form method="POST" action="{{ route('goals.store') }}" style="max-width:680px">
        @csrf
        <div class="field">
            <label class="label">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="input" placeholder="e.g. Laravel / PHP Revision">
        </div>
        <div class="field">
            <label class="label">Description</label>
            <textarea name="description" class="textarea" placeholder="Why this goal matters, and what done looks like">{{ old('description') }}</textarea>
        </div>
        <div class="form-grid">
            <div class="field">
                <label class="label">Type</label>
                <select name="type" class="select">
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label">Tracking mode</label>
                <select name="tracking_mode" class="select">
                    @foreach ($trackingModes as $mode)
                        <option value="{{ $mode->value }}" @selected(old('tracking_mode') === $mode->value)>{{ $mode->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-grid">
            <div class="field">
                <label class="label">Priority</label>
                <select name="priority" class="select">
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected(old('priority') === $priority->value)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label">Start date</label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" class="input">
            </div>
        </div>
        <div class="form-grid">
            <div class="field">
                <label class="label">Target date</label>
                <input type="date" name="target_date" value="{{ old('target_date') }}" class="input">
            </div>
            <div class="field">
                <label class="label">Target value <span style="color:var(--dim2)">(optional)</span></label>
                <input type="number" step="0.01" name="target_value" value="{{ old('target_value') }}" class="input" placeholder="e.g. 20">
            </div>
        </div>
        <div class="field">
            <label class="label">Target unit</label>
            <input type="text" name="target_unit" value="{{ old('target_unit') }}" class="input" placeholder="e.g. topics, minutes, workouts, questions">
        </div>
        <button type="submit" class="btn primary">Create goal</button>
    </form>
</x-layouts.app>
