<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGoalMilestoneRequest;
use App\Models\Goal;
use App\Models\GoalMilestone;
use Illuminate\Http\RedirectResponse;

class GoalMilestoneController extends Controller
{
    public function store(StoreGoalMilestoneRequest $request, Goal $goal): RedirectResponse
    {
        $goal->milestones()->create($request->validated() + ['status' => 'pending']);

        return back()->with('status', 'Milestone added.');
    }

    public function complete(Goal $goal, GoalMilestone $milestone): RedirectResponse
    {
        $this->authorize('update', $goal);
        abort_unless($milestone->goal_id === $goal->id, 404);

        $milestone->markCompleted();

        return back()->with('status', 'Milestone marked complete.');
    }

    public function destroy(Goal $goal, GoalMilestone $milestone): RedirectResponse
    {
        $this->authorize('update', $goal);
        abort_unless($milestone->goal_id === $goal->id, 404);

        $milestone->delete();

        return back()->with('status', 'Milestone removed.');
    }
}
