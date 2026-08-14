<?php

namespace App\Services;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Answers "how am I doing across everything I committed to?" (section 22).
 * Deliberately simple, deterministic rules for MVP (section 55) — no
 * AI-driven recommendation engine.
 */
class DashboardService
{
    public function __construct(private readonly StreakService $streaks)
    {
    }

    public function summary(User $user): array
    {
        $goals = $user->goals()->with(['topics', 'milestones'])->get();
        $active = $goals->where('status', GoalStatus::Active);

        return [
            'total_active_goals' => $active->count(),
            'goals_completed' => $goals->where('status', GoalStatus::Completed)->count(),
            'overall_progress' => $active->isEmpty() ? 0 : (int) round($active->avg(fn (Goal $g) => $g->progress())),
            'time_this_week' => $this->minutesSince($user, now()->startOfWeek()),
            'time_this_month' => $this->minutesSince($user, now()->startOfMonth()),
            'todays_activity' => $user->goals()
                ->join('goal_activities', 'goals.id', '=', 'goal_activities.goal_id')
                ->whereDate('goal_activities.occurred_at', today())
                ->count('goal_activities.id'),
            'goals_needing_attention' => $this->goalsNeedingAttention($active),
            'upcoming_deadlines' => $active->filter(fn (Goal $g) => $g->target_date && $g->target_date->isFuture() && $g->target_date->lte(now()->addDays(14)))
                ->sortBy('target_date')
                ->values(),
            'active_goals' => $active->sortByDesc(fn (Goal $g) => $g->updated_at)->values(),
        ];
    }

    protected function minutesSince(User $user, Carbon $since): int
    {
        return (int) $user->goals()
            ->join('goal_activities', 'goals.id', '=', 'goal_activities.goal_id')
            ->where('goal_activities.occurred_at', '>=', $since)
            ->sum('goal_activities.duration_minutes');
    }

    /**
     * Simple deterministic "needs attention" rules (section 55):
     * no activity in 5+ days, or the target date is within a week.
     */
    protected function goalsNeedingAttention($activeGoals): array
    {
        return $activeGoals->filter(function (Goal $goal) {
            $lastActivity = $goal->activities()->max('occurred_at');
            $stale = ! $lastActivity || now()->diffInDays($lastActivity) >= 5;
            $dueSoon = $goal->target_date && $goal->target_date->isFuture() && $goal->target_date->lte(now()->addDays(7));

            return $stale || $dueSoon;
        })->values()->all();
    }

    public function goalDashboard(Goal $goal): array
    {
        $data = [
            'progress' => $goal->progress(),
            'topics_completed' => $goal->topics()->where('status', 'completed')->count(),
            'topics_total' => $goal->topics()->count(),
            'time_spent_minutes' => $goal->totalMinutesSpent(),
            'sessions' => $goal->activities()->count(),
            'time_this_week' => (int) $goal->activities()->where('occurred_at', '>=', now()->startOfWeek())->sum('duration_minutes'),
            'milestones_completed' => $goal->milestones()->where('status', 'completed')->count(),
            'milestones_total' => $goal->milestones()->count(),
            'current_streak' => $this->streaks->currentStreak($goal),
            'longest_streak' => $this->streaks->longestStreak($goal),
        ];

        // Question coverage is an optional secondary metric on top of topic
        // progress (not every study goal needs it) — signalled by the goal
        // having a "questions" target_unit, e.g. PHP/Laravel's 550-question
        // bank. The question bank itself is never stored here, only the count.
        if ($goal->target_unit === 'questions' && $goal->target_value) {
            $data['questions_total'] = (int) $goal->target_value;
            $data['questions_completed'] = min(
                $data['questions_total'],
                (int) $goal->activities()->where('type', 'question_review')->sum('value')
            );
        }

        return $data;
    }
}
