@props(['goal'])

@php($pct = $goal->progress())

<a href="{{ route('goals.show', $goal) }}" class="card link">
    <div class="head">
        <div>
            <div class="name">{{ $goal->name }}</div>
            <div class="meta">{{ \Illuminate\Support\Str::upper($goal->type->label()) }} · {{ $goal->description }}</div>
        </div>
        <span class="meta">{{ $goal->target_date?->format('d M Y') ?? 'NO DATE' }}</span>
    </div>
    <div class="tape"><div class="fill" data-w="{{ $pct }}"></div></div>
    <div class="foot">
        <span class="pct">{{ $pct }}%</span>
        <span class="meta">{{ $goal->status->label() }}</span>
    </div>
</a>
