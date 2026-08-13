<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGoalActivityRequest;
use App\Models\Goal;
use App\Services\GoalActivityService;
use Illuminate\Http\RedirectResponse;

class GoalActivityController extends Controller
{
    public function __construct(private readonly GoalActivityService $activities)
    {
    }

    public function store(StoreGoalActivityRequest $request, Goal $goal): RedirectResponse
    {
        $this->activities->log($goal, $request->validated() + ['user_id' => $request->user()->id]);

        return back()->with('status', 'Progress logged.');
    }

    public function destroy(Goal $goal, \App\Models\GoalActivity $activity): RedirectResponse
    {
        $this->authorize('update', $goal);
        abort_unless($activity->goal_id === $goal->id, 404);

        $activity->delete();

        return back()->with('status', 'Activity removed.');
    }
}
