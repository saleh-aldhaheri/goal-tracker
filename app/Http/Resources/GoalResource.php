<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Goal */
class GoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'tracking_mode' => $this->tracking_mode->value,
            'start_date' => $this->start_date?->toDateString(),
            'target_date' => $this->target_date?->toDateString(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'color' => $this->color,
            'icon' => $this->icon,
            'target_value' => $this->target_value,
            'target_unit' => $this->target_unit,
            'settings' => $this->settings,
            'progress' => $this->progress(),
            'time_spent_minutes' => $this->whenLoaded('activities', fn () => $this->totalMinutesSpent(), $this->totalMinutesSpent()),
            'topics_count' => $this->whenCounted('topics'),
            'milestones_count' => $this->whenCounted('milestones'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
