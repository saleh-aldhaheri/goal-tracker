<?php

namespace App\Http\Requests;

use App\Enums\GoalPriority;
use App\Enums\GoalStatus;
use App\Enums\TrackingMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('goal'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', new Enum(GoalStatus::class)],
            'priority' => ['sometimes', new Enum(GoalPriority::class)],
            'tracking_mode' => ['sometimes', new Enum(TrackingMode::class)],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'color' => ['nullable', 'string', 'max:32'],
            'icon' => ['nullable', 'string', 'max:64'],
            'target_value' => ['nullable', 'numeric', 'min:0'],
            'target_unit' => ['nullable', 'string', 'max:32'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
