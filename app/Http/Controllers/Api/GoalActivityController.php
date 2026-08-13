<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoalActivityRequest;
use App\Http\Resources\GoalActivityResource;
use App\Models\Goal;
use App\Services\GoalActivityService;
use Illuminate\Http\Request;

class GoalActivityController extends Controller
{
    public function __construct(private readonly GoalActivityService $activities)
    {
    }

    public function index(Request $request, Goal $goal)
    {
        $this->authorize('view', $goal);

        return GoalActivityResource::collection(
            $goal->activities()->paginate(30)
        );
    }

    public function store(StoreGoalActivityRequest $request, Goal $goal)
    {
        $activity = $this->activities->log($goal, $request->validated() + ['user_id' => $request->user()->id]);

        return GoalActivityResource::make($activity)->response()->setStatusCode(201);
    }
}
