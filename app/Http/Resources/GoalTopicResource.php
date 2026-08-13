<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GoalTopic */
class GoalTopicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'goal_id' => $this->goal_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'target_value' => $this->target_value,
            'completed_value' => $this->completed_value,
            'sort_order' => $this->sort_order,
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
