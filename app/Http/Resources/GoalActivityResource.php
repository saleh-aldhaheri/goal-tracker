<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GoalActivity */
class GoalActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'goal_id' => $this->goal_id,
            'topic_id' => $this->topic_id,
            'milestone_id' => $this->milestone_id,
            'type' => $this->type,
            'value' => $this->value,
            'unit' => $this->unit,
            'title' => $this->title,
            'description' => $this->description,
            'occurred_at' => $this->occurred_at->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'metadata' => $this->metadata,
        ];
    }
}
