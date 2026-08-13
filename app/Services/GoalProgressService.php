<?php

namespace App\Services;

use App\Enums\TrackingMode;
use App\Models\Goal;

/**
 * Calculates a goal's progress percentage from its tracking configuration
 * rather than a manually maintained number (spec sections 3.3 and 52).
 * Progress is always clamped to 0-100 (section 53).
 */
class GoalProgressService
{
    public function calculate(Goal $goal): int
    {
        $percentage = match ($goal->tracking_mode) {
            TrackingMode::Topics => $this->fromTopics($goal),
            TrackingMode::Milestone => $this->fromMilestones($goal),
            TrackingMode::Count => $this->fromCount($goal),
            TrackingMode::Time => $this->fromTime($goal),
            TrackingMode::Boolean => $this->fromBoolean($goal),
            TrackingMode::Habit, TrackingMode::Recurring => app(StreakService::class)->completionRate($goal),
            TrackingMode::Percentage => (int) ($goal->settings['manual_percentage'] ?? 0),
        };

        return max(0, min(100, (int) round($percentage)));
    }

    protected function fromTopics(Goal $goal): float
    {
        $total = $goal->topics()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $goal->topics()->where('status', 'completed')->count();

        return ($completed / $total) * 100;
    }

    protected function fromMilestones(Goal $goal): float
    {
        $total = $goal->milestones()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $goal->milestones()->where('status', 'completed')->count();

        return ($completed / $total) * 100;
    }

    protected function fromCount(Goal $goal): float
    {
        $target = (float) ($goal->target_value ?? 0);

        if ($target <= 0) {
            return 0;
        }

        $achieved = (float) $goal->activities()->sum('value');

        return ($achieved / $target) * 100;
    }

    protected function fromTime(Goal $goal): float
    {
        $targetMinutes = (float) ($goal->target_value ?? 0);

        if ($targetMinutes <= 0) {
            return 0;
        }

        $spent = (float) $goal->totalMinutesSpent();

        return ($spent / $targetMinutes) * 100;
    }

    protected function fromBoolean(Goal $goal): float
    {
        return $goal->status?->value === 'completed' ? 100 : 0;
    }
}
