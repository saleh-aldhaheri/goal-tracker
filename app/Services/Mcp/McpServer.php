<?php

namespace App\Services\Mcp;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * A real Model Context Protocol (MCP) server over JSON-RPC 2.0. It handles
 * the core MCP methods (`initialize`, `ping`, `tools/list`, `tools/call`)
 * and dispatches `tools/call` to the existing McpToolService, so the AI
 * agent acts through the same authenticated, validated, user-scoped
 * operations as the REST API (spec sections 36-40).
 *
 * Authentication: the caller resolves a Sanctum personal access token
 * (issued from Settings → API & MCP tokens) via `MCP_TOKEN`; every tool is
 * additionally gated by the token's abilities, so a read-only token can
 * never call a write tool.
 */
class McpServer
{
    public const PROTOCOL_VERSION = '2024-11-05';

    public function __construct(private readonly McpToolService $tools)
    {
    }

    /**
     * Handle one newline-delimited JSON-RPC message.
     * Returns the response JSON string, or null for notifications.
     */
    public function handle(string $json, ?PersonalAccessToken $token = null): ?string
    {
        $message = json_decode($json, true);

        if (! is_array($message) || ! isset($message['method'])) {
            return null;
        }

        // Notifications (no id) have no response.
        if (! array_key_exists('id', $message) || $message['id'] === null) {
            return null;
        }

        $id = $message['id'];
        $method = $message['method'];
        $params = $message['params'] ?? [];

        try {
            return $this->response($id, $this->dispatch($method, $params, $token));
        } catch (\Throwable $e) {
            return $this->response($id, null, $e->getMessage());
        }
    }

    protected function dispatch(string $method, array $params, ?PersonalAccessToken $token): array
    {
        return match ($method) {
            'initialize' => [
                'protocolVersion' => self::PROTOCOL_VERSION,
                'capabilities' => ['tools' => []],
                'serverInfo' => ['name' => 'goal-tracker', 'version' => config('app.version', '1.0.0')],
            ],
            'ping' => [],
            'tools/list' => ['tools' => McpToolService::schema()],
            'tools/call' => $this->callTool($params, $token),
            default => throw new \InvalidArgumentException("Unknown method [{$method}]."),
        };
    }

    protected function callTool(array $params, ?PersonalAccessToken $token): array
    {
        $name = $params['name'] ?? null;
        $arguments = (array) ($params['arguments'] ?? []);
        $abilities = McpToolService::abilities();

        if (! is_string($name) || ! array_key_exists($name, $abilities)) {
            return $this->toolResult("Unknown tool [{$name}].", true);
        }

        try {
            $user = $this->resolveUser($token);
            $ability = $abilities[$name];

            if ($token && method_exists($token, 'can') && ! $token->can($ability)) {
                return $this->toolResult("This token does not have the [{$ability}] ability.", true);
            }

            $result = $this->tools->{$name}($user, $arguments);

            return $this->toolResult(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            return $this->toolResult($e->getMessage(), true);
        }
    }

    protected function toolResult(string $text, bool $isError = false): array
    {
        return [
            'content' => [['type' => 'text', 'text' => $text]],
            'isError' => $isError,
        ];
    }

    protected function resolveUser(?PersonalAccessToken $token): User
    {
        if ($token && $token->tokenable instanceof User) {
            return $token->tokenable;
        }

        throw new \RuntimeException(
            'Unauthenticated. Set MCP_TOKEN to a valid API token (issue one in Settings → API & MCP tokens).'
        );
    }

    protected function response(mixed $id, array $result, ?string $errorMessage = null): string
    {
        if ($errorMessage !== null) {
            $payload = ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => -32000, 'message' => $errorMessage]];
        } else {
            $payload = ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
