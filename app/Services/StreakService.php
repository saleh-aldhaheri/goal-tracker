<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\CarbonImmutable;

/**
 * Streaks and completion rates for habit/recurring goals are always
 * derived from goal_activities, never stored as a cached counter
 * (spec sections 19-21).
 */
class StreakService
{
    /**
     * Completion rate over the goal's expected cadence, as a 0-100 percentage.
     */
    public function completionRate(Goal $goal): float
    {
        $frequencyPerWeek = (int) ($goal->settings['target_count'] ?? 1);
        $start = $goal->start_date ?? $goal->created_at->toDateImmutable();
        $weeksElapsed = max(1, CarbonImmutable::parse($start)->diffInWeeks(now(), true));
        $weeksElapsed = (int) ceil($weeksElapsed) ?: 1;

        $expected = $frequencyPerWeek * $weeksElapsed;

        if ($expected <= 0) {
            return 0;
        }

        $completed = $goal->activities()
            ->whereIn('type', ['recurring_completion', 'workout'])
            ->count();

        return min(100, ($completed / $expected) * 100);
    }

    /**
     * Current consecutive streak in days, based on distinct completion dates.
     */
    public function currentStreak(Goal $goal): int
    {
        $dates = $goal->activities()
            ->whereIn('type', ['recurring_completion', 'workout'])
            ->pluck('occurred_at')
            ->map(fn ($date) => $date->toDateString())
            ->unique()
            ->sortDesc()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $cursor = today();

        foreach ($dates as $date) {
            $date = CarbonImmutable::parse($date);

            if ($date->isSameDay($cursor) || $date->isSameDay($cursor->subDay())) {
                $streak++;
                $cursor = $date;

                continue;
            }

            break;
        }

        return $streak;
    }

    public function longestStreak(Goal $goal): int
    {
        $dates = $goal->activities()
            ->whereIn('type', ['recurring_completion', 'workout'])
            ->pluck('occurred_at')
            ->map(fn ($date) => $date->toDateString())
            ->unique()
            ->sort()
            ->values();

        $longest = 0;
        $current = 0;
        $previous = null;

        foreach ($dates as $date) {
            $date = CarbonImmutable::parse($date);

            if ($previous !== null && $previous->diffInDays($date) === 1) {
                $current++;
            } else {
                $current = 1;
            }

            $longest = max($longest, $current);
            $previous = $date;
        }

        return $longest;
    }
}
