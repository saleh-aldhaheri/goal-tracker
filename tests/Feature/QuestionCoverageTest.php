<?php

namespace Tests\Feature;

use App\Enums\TrackingMode;
use App\Models\Goal;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_coverage_is_tracked_separately_from_topic_progress(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create([
            'tracking_mode' => TrackingMode::Topics,
            'target_value' => 550,
            'target_unit' => 'questions',
        ]);

        $goal->activities()->create([
            'user_id' => $user->id,
            'type' => 'question_review',
            'value' => 12,
            'occurred_at' => now(),
        ]);
        $goal->activities()->create([
            'user_id' => $user->id,
            'type' => 'question_review',
            'value' => 8,
            'occurred_at' => now(),
        ]);

        $dashboard = app(DashboardService::class)->goalDashboard($goal->fresh());

        $this->assertSame(550, $dashboard['questions_total']);
        $this->assertSame(20, $dashboard['questions_completed']);
    }

    public function test_goals_without_a_questions_target_omit_the_metric(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create(['tracking_mode' => TrackingMode::Habit]);

        $dashboard = app(DashboardService::class)->goalDashboard($goal);

        $this->assertArrayNotHasKey('questions_total', $dashboard);
    }
}
