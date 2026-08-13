<x-layouts.app title="Edit goal">
    <h1 class="text-lg font-semibold mb-6">Edit goal</h1>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-600">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('goals.update', $goal) }}" class="space-y-4 max-w-xl">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" value="{{ old('name', $goal->name) }}" required class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" class="w-full rounded-lg border-slate-300 text-sm">{{ old('description', $goal->description) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($goal->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Priority</label>
                <select name="priority" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected($goal->priority === $priority)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tracking mode</label>
                <select name="tracking_mode" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($trackingModes as $mode)
                        <option value="{{ $mode->value }}" @selected($goal->tracking_mode === $mode)>{{ $mode->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Target date</label>
                <input type="date" name="target_date" value="{{ old('target_date', $goal->target_date?->toDateString()) }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Target value</label>
                <input type="number" step="0.01" name="target_value" value="{{ old('target_value', $goal->target_value) }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Target unit</label>
                <input name="target_unit" value="{{ old('target_unit', $goal->target_unit) }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>
        <div class="flex items-center justify-between">
            <button class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium">Save changes</button>
            <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Delete this goal? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button class="text-sm text-red-600">Delete goal</button>
            </form>
        </div>
    </form>
</x-layouts.app>
