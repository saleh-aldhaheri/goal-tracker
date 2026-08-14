<?php

namespace Tests\Unit;

use App\Enums\TrackingMode;
use App\Models\Goal;
use App\Models\User;
use App\Services\GoalProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_topic_progress_handles_zero_topics_safely(): void
    {
        $goal = Goal::factory()->for(User::factory())->create(['tracking_mode' => TrackingMode::Topics]);

        $this->assertSame(0, app(GoalProgressService::class)->calculate($goal));
    }

    public function test_topic_progress_calculates_percentage(): void
    {
        $goal = Goal::factory()->for(User::factory())->create(['tracking_mode' => TrackingMode::Topics]);
        $goal->topics()->createMany([
            ['name' => 'A', 'status' => 'completed'],
            ['name' => 'B', 'status' => 'completed'],
            ['name' => 'C', 'status' => 'completed'],
            ['name' => 'D', 'status' => 'pending'],
        ]);

        $this->assertSame(75, app(GoalProgressService::class)->calculate($goal->fresh()));
    }

    public function test_progress_never_exceeds_100(): void
    {
        $goal = Goal::factory()->for(User::factory())->create([
            'tracking_mode' => TrackingMode::Count,
            'target_value' => 10,
        ]);
        $goal->activities()->create([
            'user_id' => $goal->user_id,
            'type' => 'other',
            'value' => 999,
            'occurred_at' => now(),
        ]);

        $this->assertSame(100, app(GoalProgressService::class)->calculate($goal->fresh()));
    }
}
