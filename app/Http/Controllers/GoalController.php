<?php

namespace App\Http\Controllers;

use App\Enums\GoalPriority;
use App\Enums\GoalStatus;
use App\Enums\GoalType;
use App\Enums\TrackingMode;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function index(Request $request): View
    {
        $goals = $request->user()->goals()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where('name', 'like', $term)->orWhere('description', 'like', $term);
            }))
            ->withCount(['topics', 'milestones'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('goals.index', [
            'goals' => $goals,
            'statuses' => GoalStatus::cases(),
            'types' => GoalType::cases(),
        ]);
    }

    public function create(): View
    {
        return view('goals.create', [
            'types' => GoalType::cases(),
            'priorities' => GoalPriority::cases(),
            'trackingModes' => TrackingMode::cases(),
        ]);
    }

    public function store(StoreGoalRequest $request): RedirectResponse
    {
        $goal = $request->user()->goals()->create($request->validated());

        return redirect()->route('goals.show', $goal)->with('status', 'Goal created.');
    }

    public function show(Goal $goal): View
    {
        $this->authorize('view', $goal);

        $goal->load(['topics', 'milestones', 'activities' => fn ($q) => $q->limit(20)]);

        return view('goals.show', ['goal' => $goal]);
    }

    public function edit(Goal $goal): View
    {
        $this->authorize('update', $goal);

        return view('goals.edit', [
            'goal' => $goal,
            'statuses' => GoalStatus::cases(),
            'priorities' => GoalPriority::cases(),
            'trackingModes' => TrackingMode::cases(),
        ]);
    }

    public function update(UpdateGoalRequest $request, Goal $goal): RedirectResponse
    {
        $goal->update($request->validated());

        return redirect()->route('goals.show', $goal)->with('status', 'Goal updated.');
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return redirect()->route('goals.index')->with('status', 'Goal deleted.');
    }

    public function pause(Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        $goal->update(['status' => GoalStatus::Paused]);

        return back()->with('status', 'Goal paused.');
    }

    public function resume(Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        $goal->update(['status' => GoalStatus::Active]);

        return back()->with('status', 'Goal resumed.');
    }

    public function complete(Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        $goal->update(['status' => GoalStatus::Completed, 'completed_at' => now()]);

        return back()->with('status', 'Goal marked complete.');
    }

    public function archive(Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        $goal->update(['status' => GoalStatus::Archived]);

        return back()->with('status', 'Goal archived.');
    }
}
