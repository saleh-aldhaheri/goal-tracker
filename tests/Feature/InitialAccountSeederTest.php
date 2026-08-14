<?php

namespace Tests\Feature;

use Database\Seeders\InitialAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitialAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_five_goals_with_zero_progress_and_no_fake_activity(): void
    {
        $this->seed(InitialAccountSeeder::class);

        $user = \App\Models\User::where('email', 'saleh@goal-tracker.local')->firstOrFail();

        $this->assertSame(5, $user->goals()->count());

        foreach ($user->goals as $goal) {
            $this->assertSame(0, $goal->progress());
            $this->assertSame(0, $goal->activities()->count());
        }

        $laravel = $user->goals()->where('name', 'PHP + Laravel Revision')->firstOrFail();
        $this->assertSame(550.0, (float) $laravel->target_value);
        $this->assertSame('questions', $laravel->target_unit);
        $this->assertGreaterThan(40, $laravel->topics()->count());

        $dotnet = $user->goals()->where('name', '.NET Revision')->firstOrFail();
        $this->assertGreaterThan(20, $dotnet->topics()->count());
    }

    public function test_it_is_idempotent_when_run_twice(): void
    {
        $this->seed(InitialAccountSeeder::class);
        $laravel = \App\Models\User::where('email', 'saleh@goal-tracker.local')->firstOrFail()
            ->goals()->where('name', 'PHP + Laravel Revision')->firstOrFail();
        $firstRunTopicCount = $laravel->topics()->count();

        $this->seed(InitialAccountSeeder::class);

        $user = \App\Models\User::where('email', 'saleh@goal-tracker.local')->firstOrFail();
        $this->assertSame(5, $user->goals()->count());
        $this->assertSame($firstRunTopicCount, $laravel->fresh()->topics()->count());
    }
}
