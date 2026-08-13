<?php

namespace App\Services\Mcp;

use App\Http\Resources\GoalActivityResource;
use App\Http\Resources\GoalMilestoneResource;
use App\Http\Resources\GoalResource;
use App\Http\Resources\GoalTopicResource;
use App\Models\Goal;
use App\Models\GoalMilestone;
use App\Models\GoalTopic;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\GoalActivityService;
use App\Services\StreakService;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Implements every MCP tool from spec section 38. Each public method here
 * is exposed via POST /api/mcp/tools/{tool} (see McpController), gated by
 * the ability declared in self::abilities(). The Goal Tracker application
 * remains the source of truth — an AI agent can only act through these
 * same authenticated, validated, user-scoped operations (spec section 40).
 */
class McpToolService
{
    public function __construct(
        private readonly GoalActivityService $activities,
        private readonly DashboardService $dashboard,
        private readonly StreakService $streaks,
    ) {
    }

    /** Tool name => required Sanctum token ability. */
    public static function abilities(): array
    {
        return [
            'list_goals' => 'goals:read',
            'get_goal' => 'goals:read',
            'create_goal' => 'goals:write',
            'update_goal' => 'goals:write',
            'delete_goal' => 'goals:write',
            'pause_goal' => 'goals:write',
            'resume_goal' => 'goals:write',
            'complete_goal' => 'goals:write',
            'list_goal_topics' => 'goals:read',
            'create_goal_topic' => 'goals:write',
            'update_goal_topic' => 'goals:write',
            'complete_goal_topic' => 'goals:write',
            'list_goal_milestones' => 'goals:read',
            'create_goal_milestone' => 'goals:write',
            'update_goal_milestone' => 'goals:write',
            'complete_goal_milestone' => 'goals:write',
            'log_goal_activity' => 'activities:write',
            'get_goal_activity' => 'activities:read',
            'get_goal_progress' => 'dashboard:read',
            'get_goal_statistics' => 'dashboard:read',
            'get_dashboard' => 'dashboard:read',
            'get_time_summary' => 'dashboard:read',
            'get_streak' => 'dashboard:read',
        ];
    }

    protected function goal(User $user, array $args): Goal
    {
        $goal = $user->goals()->find($args['goal_id'] ?? null);

        // 404 rather than 403 for another user's goal: never confirm existence
        // of resources the token holder doesn't own (spec section 37).
        if (! $goal) {
            throw new NotFoundHttpException('Goal not found.');
        }

        return $goal;
    }

    public function list_goals(User $user, array $args): array
    {
        $goals = $user->goals()
            ->when(! empty($args['status']), fn ($q) => $q->where('status', $args['status']))
            ->when(! empty($args['type']), fn ($q) => $q->where('type', $args['type']))
            ->latest()
            ->get();

        return GoalResource::collection($goals)->resolve();
    }

    public function get_goal(User $user, array $args): array
    {
        return GoalResource::make($this->goal($user, $args)->load(['topics', 'milestones']))->resolve();
    }

    public function create_goal(User $user, array $args): array
    {
        $data = Validator::make($args, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string'],
            'tracking_mode' => ['required', 'string'],
            'target_date' => ['nullable', 'date'],
            'target_value' => ['nullable', 'numeric'],
            'target_unit' => ['nullable', 'string'],
            'settings' => ['nullable', 'array'],
        ])->validate();

        return GoalResource::make($user->goals()->create($data))->resolve();
    }

    public function update_goal(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);

        $data = Validator::make($args, [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date'],
            'target_value' => ['nullable', 'numeric'],
            'target_unit' => ['nullable', 'string'],
            'settings' => ['nullable', 'array'],
        ])->validate();

        $goal->update($data);

        return GoalResource::make($goal)->resolve();
    }

    public function delete_goal(User $user, array $args): array
    {
        $this->goal($user, $args)->delete();

        return ['deleted' => true];
    }

    public function pause_goal(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);
        $goal->update(['status' => 'paused']);

        return GoalResource::make($goal)->resolve();
    }

    public function resume_goal(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);
        $goal->update(['status' => 'active']);

        return GoalResource::make($goal)->resolve();
    }

    public function complete_goal(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);
        $goal->update(['status' => 'completed', 'completed_at' => now()]);

        return GoalResource::make($goal)->resolve();
    }

    public function list_goal_topics(User $user, array $args): array
    {
        return GoalTopicResource::collection($this->goal($user, $args)->topics)->resolve();
    }

    public function create_goal_topic(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);

        $data = Validator::make($args, [
            'name' => ['required', 'string', 'max:255'],
            'target_value' => ['nullable', 'numeric'],
        ])->validate();

        return GoalTopicResource::make($goal->topics()->create($data + ['status' => 'pending']))->resolve();
    }

    public function update_goal_topic(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);
        $topic = $this->ownedTopic($goal, $args);

        $topic->update(Validator::make($args, [
            'name' => ['sometimes', 'string', 'max:255'],
            'completed_value' => ['sometimes', 'numeric'],
        ])->validate());

        return GoalTopicResource::make($topic)->resolve();
    }

    public function complete_goal_topic(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);
        $topic = $this->ownedTopic($goal, $args);
        $topic->markCompleted();

        return GoalTopicResource::make($topic)->resolve();
    }

    public function list_goal_milestones(User $user, array $args): array
    {
        return GoalMilestoneResource::collection($this->goal($user, $args)->milestones)->resolve();
    }

    public function create_goal_milestone(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);

        $data = Validator::make($args, [
            'name' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ])->validate();

        return GoalMilestoneResource::make($goal->milestones()->create($data + ['status' => 'pending']))->resolve();
    }

    public function update_goal_milestone(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);
        $milestone = $this->ownedMilestone($goal, $args);

        $milestone->update(Validator::make($args, [
            'name' => ['sometimes', 'string', 'max:255'],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'due_date' => ['nullable', 'date'],
        ])->validate());

        return GoalMilestoneResource::make($milestone)->resolve();
    }

    public function complete_goal_milestone(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);
        $milestone = $this->ownedMilestone($goal, $args);
        $milestone->markCompleted();

        return GoalMilestoneResource::make($milestone)->resolve();
    }

    public function log_goal_activity(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);

        $data = Validator::make($args, [
            'type' => ['required', 'string', 'max:64'],
            'topic_id' => ['nullable', 'integer'],
            'value' => ['nullable', 'numeric'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'occurred_at' => ['nullable', 'date'],
        ])->validate();

        $activity = $this->activities->log($goal, $data + ['user_id' => $user->id]);

        return GoalActivityResource::make($activity)->resolve();
    }

    public function get_goal_activity(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);

        return GoalActivityResource::collection(
            $goal->activities()->limit($args['limit'] ?? 20)->get()
        )->resolve();
    }

    public function get_goal_progress(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);

        return ['goal_id' => $goal->id, 'progress' => $goal->progress()];
    }

    public function get_goal_statistics(User $user, array $args): array
    {
        return $this->dashboard->goalDashboard($this->goal($user, $args));
    }

    public function get_dashboard(User $user, array $args): array
    {
        $summary = $this->dashboard->summary($user);

        return [
            'total_active_goals' => $summary['total_active_goals'],
            'goals_completed' => $summary['goals_completed'],
            'overall_progress' => $summary['overall_progress'],
            'time_this_week_minutes' => $summary['time_this_week'],
            'time_this_month_minutes' => $summary['time_this_month'],
            'goals_needing_attention' => GoalResource::collection($summary['goals_needing_attention'])->resolve(),
        ];
    }

    public function get_time_summary(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);

        return [
            'goal_id' => $goal->id,
            'total_minutes' => $goal->totalMinutesSpent(),
            'this_week_minutes' => (int) $goal->activities()->where('occurred_at', '>=', now()->startOfWeek())->sum('duration_minutes'),
            'this_month_minutes' => (int) $goal->activities()->where('occurred_at', '>=', now()->startOfMonth())->sum('duration_minutes'),
        ];
    }

    public function get_streak(User $user, array $args): array
    {
        $goal = $this->goal($user, $args);

        return [
            'goal_id' => $goal->id,
            'current_streak' => $this->streaks->currentStreak($goal),
            'longest_streak' => $this->streaks->longestStreak($goal),
            'completion_rate' => $this->streaks->completionRate($goal),
        ];
    }

    protected function ownedTopic(Goal $goal, array $args): GoalTopic
    {
        $topic = $goal->topics()->find($args['topic_id'] ?? null);
        abort_unless($topic, 404, 'Topic not found.');

        return $topic;
    }

    protected function ownedMilestone(Goal $goal, array $args): GoalMilestone
    {
        $milestone = $goal->milestones()->find($args['milestone_id'] ?? null);
        abort_unless($milestone, 404, 'Milestone not found.');

        return $milestone;
    }
}
