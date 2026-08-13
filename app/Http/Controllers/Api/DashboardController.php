<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard)
    {
    }

    public function index(Request $request)
    {
        $summary = $this->dashboard->summary($request->user());

        // Resource-collections don't fit this shape; return a plain, versioned envelope.
        return response()->json([
            'data' => [
                'total_active_goals' => $summary['total_active_goals'],
                'goals_completed' => $summary['goals_completed'],
                'overall_progress' => $summary['overall_progress'],
                'time_this_week_minutes' => $summary['time_this_week'],
                'time_this_month_minutes' => $summary['time_this_month'],
                'todays_activity_count' => $summary['todays_activity'],
                'goals_needing_attention' => \App\Http\Resources\GoalResource::collection($summary['goals_needing_attention']),
            ],
        ]);
    }

    public function goalDashboard(Request $request, \App\Models\Goal $goal)
    {
        $this->authorize('view', $goal);

        return response()->json(['data' => $this->dashboard->goalDashboard($goal)]);
    }
}
