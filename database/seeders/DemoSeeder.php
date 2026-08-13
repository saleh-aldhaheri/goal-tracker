<?php

namespace Database\Seeders;

use App\Enums\GoalType;
use App\Enums\TrackingMode;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Clearly-marked demo data (spec section 43-44, 80). None of this is
 * hard-coded into application logic — it's created the same way a real
 * user would create it, just via the seeder instead of the UI.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@goal-tracker.test'],
            ['name' => 'Demo User', 'password' => bcrypt('password')]
        );

        $laravel = $user->goals()->create([
            'name' => 'Laravel / PHP Revision',
            'description' => 'Demo goal — revise Laravel and PHP fundamentals.',
            'type' => GoalType::Study,
            'tracking_mode' => TrackingMode::Topics,
            'start_date' => now()->subWeeks(2),
            'target_date' => now()->addMonths(1),
        ]);

        foreach (['PHP Fundamentals', 'PHP OOP', 'Composer', 'Eloquent', 'Relationships', 'Queues', 'Testing'] as $i => $topic) {
            $laravel->topics()->create([
                'name' => $topic,
                'status' => $i < 3 ? 'completed' : 'pending',
                'sort_order' => $i,
                'completed_at' => $i < 3 ? now()->subDays(10 - $i) : null,
            ]);
        }

        $laravel->activities()->create([
            'user_id' => $user->id,
            'type' => 'study_session',
            'duration_minutes' => 90,
            'title' => 'Eloquent relationships',
            'occurred_at' => now()->subDays(1),
        ]);

        $dotnet = $user->goals()->create([
            'name' => '.NET Revision',
            'type' => GoalType::Study,
            'tracking_mode' => TrackingMode::Topics,
            'target_date' => now()->addMonths(2),
        ]);
        $dotnet->topics()->create(['name' => 'C# Fundamentals', 'status' => 'pending']);

        $project = $user->goals()->create([
            'name' => 'Build Goal Tracker',
            'type' => GoalType::Project,
            'tracking_mode' => TrackingMode::Milestone,
            'target_date' => now()->addWeeks(6),
        ]);
        foreach (['Planning', 'Backend', 'Frontend', 'Testing', 'Deployment'] as $i => $milestone) {
            $project->milestones()->create([
                'name' => $milestone,
                'status' => $i === 0 ? 'completed' : 'pending',
                'sort_order' => $i,
                'completed_at' => $i === 0 ? now()->subDays(5) : null,
            ]);
        }

        $gym = $user->goals()->create([
            'name' => 'Gym',
            'type' => GoalType::Fitness,
            'tracking_mode' => TrackingMode::Habit,
            'settings' => ['target_count' => 3],
        ]);
        foreach ([1, 3, 5] as $daysAgo) {
            $gym->activities()->create([
                'user_id' => $user->id,
                'type' => 'workout',
                'duration_minutes' => 60,
                'occurred_at' => now()->subDays($daysAgo),
            ]);
        }

        $family = $user->goals()->create([
            'name' => 'Weekly Family Call',
            'type' => GoalType::Recurring,
            'tracking_mode' => TrackingMode::Recurring,
            'settings' => ['frequency' => 'weekly', 'target_count' => 1],
        ]);
        $family->activities()->create([
            'user_id' => $user->id,
            'type' => 'recurring_completion',
            'value' => 1,
            'occurred_at' => now()->subDays(2),
        ]);
    }
}
