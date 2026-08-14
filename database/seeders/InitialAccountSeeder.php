<?php

namespace Database\Seeders;

use App\Enums\GoalPriority;
use App\Enums\GoalType;
use App\Enums\TrackingMode;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds Saleh's real initial account and goals — not demo data (see
 * DemoSeeder for that). Every goal starts at zero progress, with no
 * fabricated history, per the initial user context brief: the account
 * should represent commitments made, not achievements faked.
 *
 * Run explicitly, separately from the default `db:seed` (which only runs
 * DemoSeeder), so this never accidentally runs in CI/testing or overwrites
 * a real account with re-seeded goals on redeploy:
 *
 *     php artisan db:seed --class=InitialAccountSeeder
 *
 * Credentials: never hardcoded. Set INITIAL_USER_EMAIL and, optionally,
 * INITIAL_USER_PASSWORD in .env before running. If no password is given,
 * a random one is generated and printed once — log in and change it
 * immediately at Settings > Change password.
 */
class InitialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('INITIAL_USER_EMAIL', 'saleh@goal-tracker.local');
        $name = env('INITIAL_USER_NAME', 'Saleh');
        $password = env('INITIAL_USER_PASSWORD');
        $generatedPassword = null;

        if (! $password) {
            $generatedPassword = Str::password(16);
            $password = $generatedPassword;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password)]
        );

        if ($generatedPassword) {
            $this->command?->warn("Generated password for {$email}: {$generatedPassword}");
            $this->command?->warn('Log in and change this immediately at Settings > Change password.');
        }

        $this->seedPhpLaravel($user);
        $this->seedDotNet($user);
        $this->seedProject($user);
        $this->seedGym($user);
        $this->seedFamilyCall($user);
    }

    protected function seedPhpLaravel(User $user): void
    {
        $goal = Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'PHP + Laravel Revision'],
            [
                'description' => 'Revising PHP and Laravel for junior developer interviews, backed by an external ~550-question bank.',
                'type' => GoalType::Study,
                'priority' => GoalPriority::High,
                'tracking_mode' => TrackingMode::Topics,
                'target_value' => 550,
                'target_unit' => 'questions',
            ]
        );

        if ($goal->topics()->count() === 0) {
            $this->seedTopics($goal, [
                'Tier S / Core — PHP' => [
                    'PHP Fundamentals', 'PHP OOP', 'Modern PHP', 'Composer', 'Exceptions', 'Dependency Injection',
                ],
                'Tier S / Core — Laravel' => [
                    'MVC', 'Routing', 'Controllers', 'Middleware', 'Request Lifecycle',
                    'Validation / Form Requests', 'Eloquent', 'Eloquent Relationships', 'Migrations',
                    'Factories / Seeders', 'Query Builder', 'N+1 Query Problem', 'Authentication',
                    'Authorization', 'Service Container', 'Service Providers', 'Queues / Jobs',
                ],
                'Tier S / Core — Database' => [
                    'SQL', 'JOINs', 'Indexes', 'Foreign Keys', 'Transactions', 'Normalization',
                ],
                'Tier A' => [
                    'Events / Listeners', 'Caching', 'Redis', 'API Resources', 'Testing',
                    'Filesystem / Storage', 'Blade', 'Policies', 'Sanctum', 'Rate Limiting', 'Performance',
                ],
                'Tier B' => [
                    'Advanced Container Internals', 'Advanced PHP Internals', 'Generators',
                    'Contextual Binding', 'Advanced Events', 'Advanced Architecture', 'Advanced Distributed Systems',
                ],
            ]);
        }
    }

    protected function seedDotNet(User $user): void
    {
        $goal = Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => '.NET Revision'],
            [
                'description' => 'Revising .NET and C#, tracked independently from PHP/Laravel.',
                'type' => GoalType::Study,
                'priority' => GoalPriority::High,
                'tracking_mode' => TrackingMode::Topics,
            ]
        );

        if ($goal->topics()->count() === 0) {
            $this->seedTopics($goal, [
                'Core .NET / C#' => [
                    'C# Fundamentals', 'Object-Oriented Programming', 'Collections', 'Generics', 'LINQ',
                    'Async / Await', 'Exceptions', 'Dependency Injection', 'Interfaces', 'Delegates / Events',
                    'Modern C#', '.NET Fundamentals', 'ASP.NET Core', 'MVC', 'Web API', 'Middleware',
                    'Routing', 'Model Binding', 'Validation', 'Authentication', 'Authorization',
                    'Entity Framework Core', 'Database / SQL', 'Migrations', 'Relationships', 'Performance',
                    'Testing', 'Logging', 'Configuration', 'Caching',
                ],
            ]);
        }
    }

    protected function seedProject(User $user): void
    {
        Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Goal Tracker'],
            [
                'description' => 'Building and deploying this Goal Tracker application itself.',
                'type' => GoalType::Project,
                'priority' => GoalPriority::Medium,
                'tracking_mode' => TrackingMode::Milestone,
            ]
        );
    }

    protected function seedGym(User $user): void
    {
        Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Gym'],
            [
                'description' => 'Ongoing recurring habit — frequency is intentionally left open, edit it to your intended schedule.',
                'type' => GoalType::Fitness,
                'priority' => GoalPriority::Medium,
                'tracking_mode' => TrackingMode::Habit,
            ]
        );
    }

    protected function seedFamilyCall(User $user): void
    {
        Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Family Call'],
            [
                'description' => 'One call per week — an ongoing commitment with no final completion date.',
                'type' => GoalType::Recurring,
                'priority' => GoalPriority::Medium,
                'tracking_mode' => TrackingMode::Recurring,
                'settings' => ['frequency' => 'weekly', 'target_count' => 1],
            ]
        );
    }

    /** @param array<string, array<int, string>> $tiers */
    protected function seedTopics(Goal $goal, array $tiers): void
    {
        $order = 0;

        foreach ($tiers as $tierLabel => $topics) {
            foreach ($topics as $topic) {
                $goal->topics()->create([
                    'name' => $topic,
                    'description' => $tierLabel,
                    'status' => 'pending',
                    'sort_order' => $order++,
                ]);
            }
        }
    }
}
