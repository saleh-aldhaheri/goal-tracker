<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\InitialAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitialAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_personal_account_and_four_goals_with_zero_progress_and_no_fake_activity(): void
    {
        $this->seed(InitialAccountSeeder::class);

        $user = User::where('email', 'salehaldhaheri09@gmail.com')->firstOrFail();
        $this->assertSame('Saleh', $user->name);

        $this->assertSame(8, $user->goals()->count());

        foreach ($user->goals as $goal) {
            $this->assertSame(0, $goal->progress());
            $this->assertSame(0, $goal->activities()->count());
        }

        $laravel = $user->goals()->where('name', 'Laravel / PHP Revision')->firstOrFail();
        $this->assertSame(25, $laravel->topics()->count());
        $this->assertNull($laravel->target_value);
        $this->assertNull($laravel->target_unit);

        $dotnet = $user->goals()->where('name', '.NET Revision')->firstOrFail();
        $this->assertSame(30, $dotnet->topics()->count());

        $family = $user->goals()->where('name', 'Call Family')->firstOrFail();
        $this->assertSame(['frequency' => 'weekly', 'target_count' => 1], $family->settings);

        $chat = $user->goals()->where('name', 'Chat App')->firstOrFail();
        $this->assertSame('active', $chat->status->value);
        $this->assertSame('project', $chat->type->value);

        $portfolio = $user->goals()->where('name', 'Portfolio')->firstOrFail();
        $this->assertSame('active', $portfolio->status->value);

        foreach (['App — On Hold #1', 'App — On Hold #2'] as $name) {
            $this->assertSame('paused', $user->goals()->where('name', $name)->firstOrFail()->status->value);
        }
    }

    public function test_it_is_idempotent_when_run_twice(): void
    {
        $this->seed(InitialAccountSeeder::class);
        $laravel = User::where('email', 'salehaldhaheri09@gmail.com')->firstOrFail()
            ->goals()->where('name', 'Laravel / PHP Revision')->firstOrFail();
        $firstRunTopicCount = $laravel->topics()->count();

        $this->seed(InitialAccountSeeder::class);

        $user = User::where('email', 'salehaldhaheri09@gmail.com')->firstOrFail();
        $this->assertSame(8, $user->goals()->count());
        $this->assertSame($firstRunTopicCount, $laravel->fresh()->topics()->count());
    }
}
