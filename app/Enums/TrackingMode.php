<?php

namespace App\Enums;

/**
 * Determines how GoalProgressService calculates a goal's progress
 * percentage. See spec section 52.
 */
enum TrackingMode: string
{
    case Percentage = 'percentage';
    case Count = 'count';
    case Time = 'time';
    case Boolean = 'boolean';
    case Milestone = 'milestone';
    case Topics = 'topics';
    case Habit = 'habit';
    case Recurring = 'recurring';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Manual percentage',
            self::Count => 'Count-based',
            self::Time => 'Time-based',
            self::Boolean => 'Done / not done',
            self::Milestone => 'Milestones',
            self::Topics => 'Topics',
            self::Habit => 'Habit frequency',
            self::Recurring => 'Recurring commitment',
        };
    }
}
