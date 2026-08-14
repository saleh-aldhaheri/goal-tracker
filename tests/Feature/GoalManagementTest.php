<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_goal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/goals', [
            'name' => 'Laravel Revision',
            'type' => 'study',
            'tracking_mode' => 'topics',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('goals', ['name' => 'Laravel Revision', 'user_id' => $user->id]);
    }

    public function test_user_can_update_their_own_goal(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create();

        $response = $this->actingAs($user)->put("/goals/{$goal->id}", [
            'name' => 'Updated name',
            'status' => 'paused',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('goals', ['id' => $goal->id, 'name' => 'Updated name', 'status' => 'paused']);
    }

    public function test_user_can_archive_a_goal(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->for($user)->create();

        $this->actingAs($user)->post("/goals/{$goal->id}/archive")->assertRedirect();

        $this->assertDatabaseHas('goals', ['id' => $goal->id, 'status' => 'archived']);
    }

    public function test_user_cannot_access_another_users_goal(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $goal = Goal::factory()->for($owner)->create();

        $this->actingAs($intruder)->get("/goals/{$goal->id}")->assertForbidden();
        $this->actingAs($intruder)->put("/goals/{$goal->id}", ['name' => 'Hacked'])->assertForbidden();
    }
}
