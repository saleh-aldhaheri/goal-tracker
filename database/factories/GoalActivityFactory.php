<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\GoalActivity>
 */
class GoalActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'user_id' => User::factory(),
            'type' => 'study_session',
            'duration_minutes' => fake()->numberBetween(15, 120),
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
