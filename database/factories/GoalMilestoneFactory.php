<?php

namespace Database\Factories;

use App\Models\Goal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\GoalMilestone>
 */
class GoalMilestoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'name' => fake()->words(3, true),
            'status' => 'pending',
            'due_date' => now()->addWeeks(fake()->numberBetween(1, 8)),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
