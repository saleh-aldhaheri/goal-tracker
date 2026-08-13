<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoalTopicRequest;
use App\Http\Resources\GoalTopicResource;
use App\Models\Goal;

class GoalTopicController extends Controller
{
    public function index(Goal $goal)
    {
        $this->authorize('view', $goal);

        return GoalTopicResource::collection($goal->topics);
    }

    public function store(StoreGoalTopicRequest $request, Goal $goal)
    {
        $topic = $goal->topics()->create($request->validated() + ['status' => 'pending']);

        return GoalTopicResource::make($topic)->response()->setStatusCode(201);
    }
}
