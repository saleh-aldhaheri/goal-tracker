<x-layouts.app title="Goals">
    <div class="top">
        <div><h1>Goals</h1><div class="sub">Everything committed</div></div>
        <a class="btn primary" href="{{ route('goals.create') }}">+ New goal</a>
    </div>

    <div class="filters">
        <form method="GET" action="{{ route('goals.index') }}" style="display:contents">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="SEARCH…">
            <select name="type" class="select" style="width:auto" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <button class="btn small" type="submit">Filter</button>
        </form>
        <div class="chips">
            @php($base = array_filter(['q' => request('q'), 'type' => request('type')]))
            <a class="chip {{ ! request('status') ? 'on' : '' }}" href="{{ route('goals.index', $base) }}">All</a>
            @foreach ($statuses as $status)
                <a class="chip {{ request('status') === $status->value ? 'on' : '' }}" href="{{ route('goals.index', $base + ['status' => $status->value]) }}">{{ $status->label() }}</a>
            @endforeach
        </div>
    </div>

    <div class="goalgrid">
        @forelse ($goals as $goal)
            <x-goal-card :goal="$goal" />
        @empty
            <div style="color:var(--dim);grid-column:1/-1;padding:24px 0">
                No goals match your filters.
                <a href="{{ route('goals.create') }}" style="color:var(--brass)">Create one</a>.
            </div>
        @endforelse
    </div>

    {{ $goals->links() }}
</x-layouts.app>
