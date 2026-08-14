<x-layouts.app :title="'Edit ' . $goal->name">
    <div class="top">
        <div><h1>Edit goal</h1><div class="sub">{{ $goal->name }}</div></div>
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

    <form method="POST" action="{{ route('goals.update', $goal) }}" style="max-width:680px">
        @csrf
        @method('PUT')
        <div class="field">
            <label class="label">Name</label>
            <input type="text" name="name" value="{{ old('name', $goal->name) }}" required class="input">
        </div>
        <div class="field">
            <label class="label">Description</label>
            <textarea name="description" class="textarea">{{ old('description', $goal->description) }}</textarea>
        </div>
        <div class="form-grid">
            <div class="field">
                <label class="label">Status</label>
                <select name="status" class="select">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($goal->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label">Priority</label>
                <select name="priority" class="select">
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected($goal->priority === $priority)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-grid">
            <div class="field">
                <label class="label">Tracking mode</label>
                <select name="tracking_mode" class="select">
                    @foreach ($trackingModes as $mode)
                        <option value="{{ $mode->value }}" @selected($goal->tracking_mode === $mode)>{{ $mode->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label">Target date</label>
                <input type="date" name="target_date" value="{{ old('target_date', $goal->target_date?->toDateString()) }}" class="input">
            </div>
        </div>
        <div class="form-grid">
            <div class="field">
                <label class="label">Target value</label>
                <input type="number" step="0.01" name="target_value" value="{{ old('target_value', $goal->target_value) }}" class="input">
            </div>
            <div class="field">
                <label class="label">Target unit</label>
                <input type="text" name="target_unit" value="{{ old('target_unit', $goal->target_unit) }}" class="input">
            </div>
        </div>
        <button type="submit" class="btn primary">Save changes</button>
    </form>

    <div style="margin-top:20px;max-width:680px">
        <form method="POST" action="{{ route('goals.destroy', $goal) }}" data-confirm="Delete the goal '{{ $goal->name }}'? This cannot be undone.">
            @csrf
            @method('DELETE')
            <button class="btn danger">Delete goal</button>
        </form>
    </div>
</x-layouts.app>
