<?php

namespace Tests\Feature\Mcp;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_goals_tool_returns_only_the_tokens_owner_goals(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Goal::factory()->for($user)->count(2)->create();
        Goal::factory()->for($other)->create();

        $token = $user->createToken('mcp', ['goals:read'])->plainTextToken;

        $this->withToken($token)->postJson('/api/mcp/tools/list_goals', [])
            ->assertOk()
            ->assertJsonCount(2, 'result');
    }

    public function test_create_goal_tool_requires_write_ability(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mcp', ['goals:read'])->plainTextToken;

        $this->withToken($token)->postJson('/api/mcp/tools/create_goal', [
            'name' => 'AWS revision',
            'type' => 'study',
            'tracking_mode' => 'topics',
        ])->assertForbidden();
    }

    public function test_log_goal_activity_tool_creates_activity_for_owned_goal(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create();
        $token = $user->createToken('mcp', ['activities:write'])->plainTextToken;

        $this->withToken($token)->postJson("/api/mcp/tools/log_goal_activity", [
            'goal_id' => $goal->id,
            'type' => 'study_session',
            'duration_minutes' => 60,
        ])->assertOk();

        $this->assertDatabaseHas('goal_activities', ['goal_id' => $goal->id, 'duration_minutes' => 60]);
    }

    public function test_tool_cannot_touch_another_users_goal(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $goal = Goal::factory()->for($owner)->create();
        $token = $intruder->createToken('mcp', ['goals:read'])->plainTextToken;

        $this->withToken($token)->postJson('/api/mcp/tools/get_goal', ['goal_id' => $goal->id])
            ->assertNotFound();
    }

    public function test_unknown_tool_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mcp', ['goals:read'])->plainTextToken;

        $this->withToken($token)->postJson('/api/mcp/tools/delete_everything', [])
            ->assertNotFound();
    }
}
