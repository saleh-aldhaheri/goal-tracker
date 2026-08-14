<?php

namespace App\Console\Commands;

use App\Services\Mcp\McpServer;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Runs the Goal Tracker as a real MCP server over stdio (JSON-RPC 2.0),
 * newline-delimited. Register this in any MCP client (Claude Desktop,
 * Cursor, etc.) as:
 *
 *     php /path/to/artisan mcp:serve
 *
 * with MCP_TOKEN set to an API token issued from Settings → API & MCP tokens.
 */
class McpServeCommand extends Command
{
    protected $signature = 'mcp:serve {--token= : API token (defaults to the MCP_TOKEN env var)}';

    protected $description = 'Run the Goal Tracker MCP server over stdio';

    public function handle(McpServer $server): int
    {
        $plain = $this->option('token') ?: env('MCP_TOKEN');
        $token = $plain ? PersonalAccessToken::findToken($plain) : null;

        if ($plain && ! $token) {
            fwrite(STDERR, "MCP_TOKEN is not a valid API token. Issue one in Settings → API & MCP tokens.\n");

            return self::FAILURE;
        }

        fwrite(STDERR, "Goal Tracker MCP server running over stdio.\n");

        while (($line = fgets(STDIN)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $response = $server->handle($line, $token);

            if ($response !== null) {
                fwrite(STDOUT, $response."\n");
                fflush(STDOUT);
            }
        }

        return self::SUCCESS;
    }
}
