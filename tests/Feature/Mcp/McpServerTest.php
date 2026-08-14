<?php

namespace Tests\Feature\Mcp;

use App\Models\Goal;
use App\Models\User;
use App\Services\Mcp\McpServer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class McpServerTest extends TestCase
{
    use RefreshDatabase;

    protected McpServer $server;

    protected function setUp(): void
    {
        parent::setUp();
        $this->server = app(McpServer::class);
    }

    protected function rpc(string $method, array $params = [], ?PersonalAccessToken $token = null, int $id = 1): array
    {
        $json = json_encode(['jsonrpc' => '2.0', 'id' => $id, 'method' => $method, 'params' => $params]);

        return json_decode($this->server->handle($json, $token), true);
    }

    public function test_initialize_advertises_tool_capabilities(): void
    {
        $res = $this->rpc('initialize');

        $this->assertSame('2024-11-05', $res['result']['protocolVersion']);
        $this->assertArrayHasKey('tools', $res['result']['capabilities']);
        $this->assertSame('goal-tracker', $res['result']['serverInfo']['name']);
    }

    public function test_tools_list_returns_all_tools_with_schemas(): void
    {
        $tools = $this->rpc('tools/list')['result']['tools'];

        $this->assertCount(23, $tools);
        $this->assertArrayHasKey('inputSchema', $tools[0]);
        $this->assertArrayHasKey('description', $tools[0]);
    }

    public function test_tools_call_lists_only_the_tokens_owned_goals(): void
    {
        $owner = User::factory()->create();
        Goal::factory()->create(['user_id' => $owner->id, 'name' => 'Laravel / PHP Revision']);
        $token = $owner->createToken('full', ['goals:read', 'goals:write', 'activities:read', 'activities:write', 'dashboard:read'])->accessToken;

        $res = $this->rpc('tools/call', ['name' => 'list_goals', 'arguments' => []], $token);

        $this->assertFalse($res['result']['isError']);
        $this->assertStringContainsString('Laravel', $res['result']['content'][0]['text']);
    }

    public function test_read_only_token_cannot_call_write_tool(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('readonly', ['goals:read'])->accessToken;

        $res = $this->rpc('tools/call', ['name' => 'create_goal', 'arguments' => ['name' => 'X', 'type' => 'study', 'tracking_mode' => 'topics']], $token);

        $this->assertTrue($res['result']['isError']);
    }

    public function test_tool_call_requires_a_token(): void
    {
        $res = $this->rpc('tools/call', ['name' => 'list_goals', 'arguments' => []]);

        $this->assertTrue($res['result']['isError']);
    }

    public function test_unknown_tool_returns_error(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('full', ['goals:read'])->accessToken;

        $res = $this->rpc('tools/call', ['name' => 'nope', 'arguments' => []], $token);

        $this->assertTrue($res['result']['isError']);
    }

    public function test_notification_has_no_response(): void
    {
        $json = json_encode(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']);

        $this->assertNull($this->server->handle($json));
    }
}
