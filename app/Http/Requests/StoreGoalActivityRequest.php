<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('goal'));
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:64'],
            'topic_id' => ['nullable', 'exists:goal_topics,id'],
            'milestone_id' => ['nullable', 'exists:goal_milestones,id'],
            'value' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:32'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'occurred_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
