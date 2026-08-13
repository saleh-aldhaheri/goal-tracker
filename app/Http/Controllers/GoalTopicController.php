<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGoalTopicRequest;
use App\Models\Goal;
use App\Models\GoalTopic;
use Illuminate\Http\RedirectResponse;

class GoalTopicController extends Controller
{
    public function store(StoreGoalTopicRequest $request, Goal $goal): RedirectResponse
    {
        $goal->topics()->create($request->validated() + ['status' => 'pending']);

        return back()->with('status', 'Topic added.');
    }

    public function complete(Goal $goal, GoalTopic $topic): RedirectResponse
    {
        $this->authorize('update', $goal);
        abort_unless($topic->goal_id === $goal->id, 404);

        $topic->markCompleted();

        return back()->with('status', 'Topic marked complete.');
    }

    public function destroy(Goal $goal, GoalTopic $topic): RedirectResponse
    {
        $this->authorize('update', $goal);
        abort_unless($topic->goal_id === $goal->id, 404);

        $topic->delete();

        return back()->with('status', 'Topic removed.');
    }
}
