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

    /** Full tool metadata: name => [description, properties, required, ability]. */
    public static function tools(): array
    {
        $goalId = ['type' => 'integer', 'description' => 'The goal ID'];

        return [
            'list_goals' => [
                'ability' => 'goals:read',
                'description' => 'List your goals, optionally filtered by status and type.',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Filter by status: active, completed, paused, archived'],
                    'type' => ['type' => 'string', 'description' => 'Filter by type: study, project, habit, recurring, fitness, custom'],
                ],
                'required' => [],
            ],
            'get_goal' => [
                'ability' => 'goals:read',
                'description' => 'Get a single goal with its topics and milestones.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'create_goal' => [
                'ability' => 'goals:write',
                'description' => 'Create a new goal.',
                'properties' => [
                    'name' => ['type' => 'string', 'description' => 'Goal name'],
                    'type' => ['type' => 'string', 'description' => 'Goal type: study, project, habit, recurring, fitness, custom'],
                    'tracking_mode' => ['type' => 'string', 'description' => 'How progress is measured: topics, milestone, count, time, boolean, habit, recurring, percentage'],
                    'description' => ['type' => 'string'],
                    'target_date' => ['type' => 'string', 'description' => 'Target date (YYYY-MM-DD)'],
                    'target_value' => ['type' => 'number'],
                    'target_unit' => ['type' => 'string'],
                    'settings' => ['type' => 'object'],
                ],
                'required' => ['name', 'type', 'tracking_mode'],
            ],
            'update_goal' => [
                'ability' => 'goals:write',
                'description' => 'Update a goal.',
                'properties' => [
                    'goal_id' => $goalId,
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'target_date' => ['type' => 'string'],
                    'target_value' => ['type' => 'number'],
                    'target_unit' => ['type' => 'string'],
                    'settings' => ['type' => 'object'],
                ],
                'required' => ['goal_id'],
            ],
            'delete_goal' => [
                'ability' => 'goals:write',
                'description' => 'Delete a goal.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'pause_goal' => [
                'ability' => 'goals:write',
                'description' => 'Pause an active goal.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'resume_goal' => [
                'ability' => 'goals:write',
                'description' => 'Resume a paused goal.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'complete_goal' => [
                'ability' => 'goals:write',
                'description' => 'Mark a goal complete.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'list_goal_topics' => [
                'ability' => 'goals:read',
                'description' => 'List the topics of a goal.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'create_goal_topic' => [
                'ability' => 'goals:write',
                'description' => 'Add a topic to a goal.',
                'properties' => [
                    'goal_id' => $goalId,
                    'name' => ['type' => 'string', 'description' => 'Topic name'],
                    'target_value' => ['type' => 'number'],
                ],
                'required' => ['goal_id', 'name'],
            ],
            'update_goal_topic' => [
                'ability' => 'goals:write',
                'description' => 'Update a topic.',
                'properties' => [
                    'goal_id' => $goalId,
                    'topic_id' => ['type' => 'integer', 'description' => 'The topic ID'],
                    'name' => ['type' => 'string'],
                    'completed_value' => ['type' => 'number'],
                ],
                'required' => ['goal_id', 'topic_id'],
            ],
            'complete_goal_topic' => [
                'ability' => 'goals:write',
                'description' => 'Mark a topic complete.',
                'properties' => ['goal_id' => $goalId, 'topic_id' => ['type' => 'integer', 'description' => 'The topic ID']],
                'required' => ['goal_id', 'topic_id'],
            ],
            'list_goal_milestones' => [
                'ability' => 'goals:read',
                'description' => 'List the milestones of a goal.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'create_goal_milestone' => [
                'ability' => 'goals:write',
                'description' => 'Add a milestone to a goal.',
                'properties' => [
                    'goal_id' => $goalId,
                    'name' => ['type' => 'string', 'description' => 'Milestone name'],
                    'due_date' => ['type' => 'string'],
                ],
                'required' => ['goal_id', 'name'],
            ],
            'update_goal_milestone' => [
                'ability' => 'goals:write',
                'description' => 'Update a milestone.',
                'properties' => [
                    'goal_id' => $goalId,
                    'milestone_id' => ['type' => 'integer', 'description' => 'The milestone ID'],
                    'name' => ['type' => 'string'],
                    'progress' => ['type' => 'integer', 'description' => 'Progress 0-100'],
                    'due_date' => ['type' => 'string'],
                ],
                'required' => ['goal_id', 'milestone_id'],
            ],
            'complete_goal_milestone' => [
                'ability' => 'goals:write',
                'description' => 'Mark a milestone complete.',
                'properties' => ['goal_id' => $goalId, 'milestone_id' => ['type' => 'integer', 'description' => 'The milestone ID']],
                'required' => ['goal_id', 'milestone_id'],
            ],
            'log_goal_activity' => [
                'ability' => 'activities:write',
                'description' => 'Log an activity/session against a goal (time, completion, note).',
                'properties' => [
                    'goal_id' => $goalId,
                    'type' => ['type' => 'string', 'description' => 'Activity type, e.g. study_session, workout, recurring_completion, note'],
                    'topic_id' => ['type' => 'integer'],
                    'value' => ['type' => 'number'],
                    'duration_minutes' => ['type' => 'integer', 'description' => 'Minutes spent'],
                    'title' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'occurred_at' => ['type' => 'string', 'description' => 'When it happened (date)'],
                ],
                'required' => ['goal_id', 'type'],
            ],
            'get_goal_activity' => [
                'ability' => 'activities:read',
                'description' => 'List recent activity for a goal.',
                'properties' => ['goal_id' => $goalId, 'limit' => ['type' => 'integer', 'description' => 'Max entries (default 20)']],
                'required' => ['goal_id'],
            ],
            'get_goal_progress' => [
                'ability' => 'dashboard:read',
                'description' => 'Get a goal\'s current progress percentage.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'get_goal_statistics' => [
                'ability' => 'dashboard:read',
                'description' => 'Full statistics for a goal (topics, time, sessions, streak).',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'get_dashboard' => [
                'ability' => 'dashboard:read',
                'description' => 'Overall dashboard summary across all goals.',
                'properties' => [],
                'required' => [],
            ],
            'get_time_summary' => [
                'ability' => 'dashboard:read',
                'description' => 'Time spent on a goal: total, this week, this month.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
            'get_streak' => [
                'ability' => 'dashboard:read',
                'description' => 'Current and longest streak, and completion rate for a goal.',
                'properties' => ['goal_id' => $goalId],
                'required' => ['goal_id'],
            ],
        ];
    }

    /** MCP tools/list schema (name, description, inputSchema). */
    public static function schema(): array
    {
        return array_map(
            fn ($name, $meta) => [
                'name' => $name,
                'description' => $meta['description'],
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => $meta['properties'],
                    'required' => $meta['required'],
                ],
            ],
            array_keys(self::tools()),
            self::tools()
        );
    }

    /** Tool name => required Sanctum token ability. */
    public static function abilities(): array
    {
        return array_map(fn ($meta) => $meta['ability'], self::tools());
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
