<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_topic(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->study()->create();

        $this->actingAs($user)->post("/goals/{$goal->id}/topics", ['name' => 'Eloquent'])
            ->assertRedirect();

        $this->assertDatabaseHas('goal_topics', ['goal_id' => $goal->id, 'name' => 'Eloquent']);
    }

    public function test_completing_a_topic_updates_progress(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->study()->create();
        $topic = $goal->topics()->create(['name' => 'Topic A', 'status' => 'pending']);
        $goal->topics()->create(['name' => 'Topic B', 'status' => 'pending']);

        $this->assertSame(0, $goal->fresh()->progress());

        $this->actingAs($user)->post("/goals/{$goal->id}/topics/{$topic->id}/complete")->assertRedirect();

        $this->assertSame(50, $goal->fresh()->progress());
    }

    public function test_logging_an_activity_stores_duration_as_minutes(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->study()->create();

        $this->actingAs($user)->post("/goals/{$goal->id}/activities", [
            'type' => 'study_session',
            'duration_minutes' => 80,
        ])->assertRedirect();

        $this->assertDatabaseHas('goal_activities', [
            'goal_id' => $goal->id,
            'duration_minutes' => 80,
        ]);
        $this->assertSame(80, $goal->fresh()->totalMinutesSpent());
    }

    public function test_recurring_goal_records_completion_and_streak(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->recurring()->create();

        $goal->activities()->create([
            'user_id' => $user->id,
            'type' => 'recurring_completion',
            'value' => 1,
            'occurred_at' => now(),
        ]);

        $streaks = app(\App\Services\StreakService::class);
        $this->assertSame(1, $streaks->currentStreak($goal->fresh()));
    }
}
