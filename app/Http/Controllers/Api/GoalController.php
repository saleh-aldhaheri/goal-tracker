<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Http\Resources\GoalResource;
use App\Models\Goal;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $goals = $request->user()->goals()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->withCount(['topics', 'milestones'])
            ->latest()
            ->paginate(20);

        return GoalResource::collection($goals);
    }

    public function store(StoreGoalRequest $request)
    {
        $goal = $request->user()->goals()->create($request->validated());

        return GoalResource::make($goal)->response()->setStatusCode(201);
    }

    public function show(Goal $goal)
    {
        $this->authorize('view', $goal);

        return GoalResource::make($goal->load(['topics', 'milestones']));
    }

    public function update(UpdateGoalRequest $request, Goal $goal)
    {
        $goal->update($request->validated());

        return GoalResource::make($goal);
    }

    public function destroy(Goal $goal)
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return response()->noContent();
    }
}
