<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\GoalActivity;
use Illuminate\Support\Facades\DB;

/**
 * Centralises the side effects of logging progress (spec section 3.3):
 * every logged activity is permanent history, and topic/milestone
 * completion values are derived from it rather than edited by hand.
 */
class GoalActivityService
{
    public function log(Goal $goal, array $attributes): GoalActivity
    {
        return DB::transaction(function () use ($goal, $attributes) {
            $activity = $goal->activities()->create([
                'user_id' => $attributes['user_id'],
                'topic_id' => $attributes['topic_id'] ?? null,
                'milestone_id' => $attributes['milestone_id'] ?? null,
                'type' => $attributes['type'],
                'value' => $attributes['value'] ?? null,
                'unit' => $attributes['unit'] ?? null,
                'title' => $attributes['title'] ?? null,
                'description' => $attributes['description'] ?? null,
                'occurred_at' => $attributes['occurred_at'] ?? now(),
                'duration_minutes' => $attributes['duration_minutes'] ?? null,
                'metadata' => $attributes['metadata'] ?? null,
            ]);

            if ($activity->topic_id && $activity->duration_minutes) {
                $activity->topic()->increment('completed_value', 0); // time tracked via activities, not topic value
            }

            return $activity;
        });
    }
}
