<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmTest extends TestCase
{
    use RefreshDatabase;

    public function test_farm_requires_authentication(): void
    {
        $this->get(route('farm'))->assertRedirect(route('login'));
    }

    public function test_farm_page_loads_with_goal_flowers(): void
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->study()->create(['user_id' => $user->id, 'name' => 'Laravel / PHP Revision']);
        $goal->topics()->create(['name' => 'Eloquent', 'status' => 'completed']);

        $response = $this->actingAs($user)->get(route('farm'));

        $response->assertOk();
        $response->assertSee('GOAL GARDEN');
        $response->assertSee('Laravel');
        $response->assertSee('last_days');
    }
}
