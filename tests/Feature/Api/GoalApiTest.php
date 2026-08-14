<?php

namespace Tests\Feature\Api;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/goals')->assertUnauthorized();
    }

    public function test_authenticated_token_can_list_goals(): void
    {
        $user = User::factory()->create();
        Goal::factory()->for($user)->count(2)->create();
        $token = $user->createToken('test', ['goals:read'])->plainTextToken;

        $this->withToken($token)->getJson('/api/goals')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_read_only_token_cannot_write(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test', ['goals:read'])->plainTextToken;

        $this->withToken($token)->postJson('/api/goals', [
            'name' => 'New goal',
            'type' => 'study',
            'tracking_mode' => 'topics',
        ])->assertForbidden();
    }

    public function test_token_with_write_ability_can_create_goal(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test', ['goals:write'])->plainTextToken;

        $this->withToken($token)->postJson('/api/goals', [
            'name' => 'New goal',
            'type' => 'study',
            'tracking_mode' => 'topics',
        ])->assertCreated();

        $this->assertDatabaseHas('goals', ['name' => 'New goal', 'user_id' => $user->id]);
    }

    public function test_user_cannot_access_another_users_goal_via_api(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $goal = Goal::factory()->for($owner)->create();
        $token = $intruder->createToken('test', ['goals:read'])->plainTextToken;

        $this->withToken($token)->getJson("/api/goals/{$goal->id}")->assertForbidden();
    }
}
