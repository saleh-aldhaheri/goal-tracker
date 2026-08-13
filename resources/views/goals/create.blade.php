<x-layouts.app title="New goal">
    <h1 class="text-lg font-semibold mb-6">Create a goal</h1>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-600">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('goals.store') }}" class="space-y-4 max-w-xl">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" class="w-full rounded-lg border-slate-300 text-sm">{{ old('description') }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <select name="type" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tracking mode</label>
                <select name="tracking_mode" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($trackingModes as $mode)
                        <option value="{{ $mode->value }}">{{ $mode->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Priority</label>
                <select name="priority" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Target date</label>
                <input type="date" name="target_date" value="{{ old('target_date') }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Target value <span class="text-slate-400">(optional — count/time goals)</span></label>
                <input type="number" step="0.01" name="target_value" value="{{ old('target_value') }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Target unit</label>
                <input name="target_unit" value="{{ old('target_unit') }}" placeholder="e.g. minutes, workouts" class="w-full rounded-lg border-slate-300 text-sm">
            </div>
        </div>
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium">Create goal</button>
    </form>
</x-layouts.app>
