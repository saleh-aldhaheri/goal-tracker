<?php

namespace App\Enums;

/**
 * Goal types are descriptive labels only — application logic must never
 * branch on a specific type value (spec section 79). New types can be
 * added by users via the "custom" type without a code change.
 */
enum GoalType: string
{
    case Study = 'study';
    case Project = 'project';
    case Habit = 'habit';
    case Recurring = 'recurring';
    case Fitness = 'fitness';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Study => 'Study',
            self::Project => 'Project',
            self::Habit => 'Habit',
            self::Recurring => 'Recurring',
            self::Fitness => 'Fitness',
            self::Custom => 'Custom',
        };
    }
}
