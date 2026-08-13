<?php

namespace Database\Factories;

use App\Enums\GoalPriority;
use App\Enums\GoalStatus;
use App\Enums\GoalType;
use App\Enums\TrackingMode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Goal>
 */
class GoalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(GoalType::cases()),
            'status' => GoalStatus::Active,
            'priority' => fake()->randomElement(GoalPriority::cases()),
            'tracking_mode' => TrackingMode::Topics,
            'start_date' => now()->subWeeks(fake()->numberBetween(0, 4)),
            'target_date' => now()->addWeeks(fake()->numberBetween(2, 12)),
        ];
    }

    public function study(): static
    {
        return $this->state(fn () => [
            'type' => GoalType::Study,
            'tracking_mode' => TrackingMode::Topics,
        ]);
    }

    public function habit(): static
    {
        return $this->state(fn () => [
            'type' => GoalType::Habit,
            'tracking_mode' => TrackingMode::Habit,
            'settings' => ['target_count' => 3],
        ]);
    }

    public function recurring(): static
    {
        return $this->state(fn () => [
            'type' => GoalType::Recurring,
            'tracking_mode' => TrackingMode::Recurring,
            'settings' => ['frequency' => 'weekly', 'target_count' => 1],
        ]);
    }
}
