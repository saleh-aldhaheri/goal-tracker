<?php

namespace Database\Seeders;

use App\Enums\GoalPriority;
use App\Enums\GoalStatus;
use App\Enums\GoalType;
use App\Enums\TrackingMode;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds Saleh's personal account and the four commitments he is actually
 * tracking, with zero fabricated history — everything starts empty/zero.
 *
 * No demo users, no invented targets (the only explicit target is the
 * weekly family call), and no question bank. Laravel/PHP topics are the
 * 25 revision areas the user is working through; .NET topics are left
 * empty for the user to add.
 */
class InitialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'salehaldhaheri09@gmail.com'],
            [
                'name' => 'Saleh',
                'password' => Hash::make(env('INITIAL_USER_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ]
        );

        $this->seedPhpLaravel($user);
        $this->seedDotNet($user);
        $this->seedGym($user);
        $this->seedFamilyCall($user);
        $this->seedProjects($user);
    }

    protected function seedPhpLaravel(User $user): void
    {
        $goal = Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Laravel / PHP Revision'],
            [
                'description' => 'Revise PHP and Laravel to be interview-ready. Tracks topics covered and study time.',
                'type' => GoalType::Study,
                'status' => GoalStatus::Active,
                'priority' => GoalPriority::High,
                'tracking_mode' => TrackingMode::Topics,
                'start_date' => today(),
            ]
        );

        if ($goal->topics()->count() === 0) {
            $topics = [
                'PHP Fundamentals',
                'PHP OOP',
                'Modern PHP',
                'Composer',
                'HTTP / Web Fundamentals',
                'Laravel Fundamentals',
                'Request Lifecycle',
                'Eloquent',
                'Eloquent Relationships',
                'Database / Migrations',
                'Query Builder / SQL',
                'Validation',
                'Authentication / Authorization',
                'Service Container / Dependency Injection',
                'Events / Listeners',
                'Queues / Jobs',
                'Caching',
                'Files / Storage',
                'API Development',
                'Testing',
                'Security',
                'Blade / Frontend Basics',
                'Practical / Scenario Questions',
                'Interviewer Scenario Questions',
                'Project Questions',
            ];

            foreach ($topics as $order => $name) {
                $goal->topics()->create([
                    'name' => $name,
                    'status' => 'pending',
                    'sort_order' => $order,
                ]);
            }
        }
    }

    protected function seedDotNet(User $user): void
    {
        $goal = Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => '.NET Revision'],
            [
                'description' => 'Revise and strengthen .NET / C# knowledge. Add your own topics as you study.',
                'type' => GoalType::Study,
                'status' => GoalStatus::Active,
                'priority' => GoalPriority::High,
                'tracking_mode' => TrackingMode::Topics,
                'start_date' => today(),
            ]
        );

        if ($goal->topics()->count() === 0) {
            $topics = [
                'C# Fundamentals',
                'Object-Oriented Programming',
                'Collections',
                'Generics',
                'LINQ',
                'Async / Await',
                'Exceptions',
                'Dependency Injection',
                'Interfaces',
                'Delegates / Events',
                'Modern C# Features',
                '.NET Fundamentals',
                'ASP.NET Core',
                'MVC',
                'Web API',
                'Middleware',
                'Routing',
                'Model Binding',
                'Validation',
                'Authentication',
                'Authorization',
                'Entity Framework Core',
                'Database / SQL',
                'Migrations',
                'Relationships',
                'Performance',
                'Testing',
                'Logging',
                'Configuration',
                'Caching',
            ];

            foreach ($topics as $order => $name) {
                $goal->topics()->create([
                    'name' => $name,
                    'status' => 'pending',
                    'sort_order' => $order,
                ]);
            }
        }
    }

    protected function seedGym(User $user): void
    {
        Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Gym / Fitness'],
            [
                'description' => 'Recurring workout habit. Frequency is intentionally open — configure it to your schedule.',
                'type' => GoalType::Fitness,
                'status' => GoalStatus::Active,
                'priority' => GoalPriority::Medium,
                'tracking_mode' => TrackingMode::Habit,
                'start_date' => today(),
            ]
        );
    }

    protected function seedFamilyCall(User $user): void
    {
        Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Call Family'],
            [
                'description' => 'One family call per week — an ongoing recurring commitment.',
                'type' => GoalType::Recurring,
                'status' => GoalStatus::Active,
                'priority' => GoalPriority::Medium,
                'tracking_mode' => TrackingMode::Recurring,
                'start_date' => today(),
                'settings' => ['frequency' => 'weekly', 'target_count' => 1],
            ]
        );
    }

    protected function seedProjects(User $user): void
    {
        Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Chat App'],
            [
                'description' => 'Complete the chat application.',
                'type' => GoalType::Project,
                'status' => GoalStatus::Active,
                'tracking_mode' => TrackingMode::Milestone,
                'start_date' => today(),
            ]
        );

        Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Portfolio'],
            [
                'description' => 'Complete the personal/professional portfolio.',
                'type' => GoalType::Project,
                'status' => GoalStatus::Active,
                'tracking_mode' => TrackingMode::Milestone,
                'start_date' => today(),
            ]
        );

        Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'App — On Hold #1'],
            [
                'description' => 'Complete the application when it is resumed.',
                'type' => GoalType::Project,
                'status' => GoalStatus::Paused,
                'tracking_mode' => TrackingMode::Milestone,
            ]
        );

        Goal::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'App — On Hold #2'],
            [
                'description' => 'Complete the application when it is resumed.',
                'type' => GoalType::Project,
                'status' => GoalStatus::Paused,
                'tracking_mode' => TrackingMode::Milestone,
            ]
        );
    }
}
