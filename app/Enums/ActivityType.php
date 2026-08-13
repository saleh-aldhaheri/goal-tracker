<?php

namespace App\Enums;

/**
 * Free-form label for a goal_activities row. This is intentionally not
 * exhaustive — MCP tools and the UI may write any short snake_case
 * string here (e.g. "study_session", "workout", "recurring_completion",
 * "milestone", "note"). The enum only captures the common built-ins used
 * by the UI's quick-entry forms.
 */
enum ActivityType: string
{
    case StudySession = 'study_session';
    case Workout = 'workout';
    case RecurringCompletion = 'recurring_completion';
    case Milestone = 'milestone';
    case TimeLog = 'time_log';
    case Note = 'note';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::StudySession => 'Study session',
            self::Workout => 'Workout',
            self::RecurringCompletion => 'Recurring completion',
            self::Milestone => 'Milestone update',
            self::TimeLog => 'Time log',
            self::Note => 'Note',
            self::Other => 'Other',
        };
    }
}
