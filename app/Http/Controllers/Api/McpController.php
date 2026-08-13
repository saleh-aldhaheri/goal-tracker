<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Mcp\McpToolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * A lightweight, self-contained MCP-compatible tool-call endpoint
 * (spec sections 36-38). No third-party Laravel MCP package is depended
 * on here — at the time this was built there wasn't a single dominant,
 * verifiable choice, so the tool contract (tool name in the URL, JSON
 * arguments in the body, JSON result back) mirrors the shape any MCP
 * bridge/adapter expects and can be wrapped by one later without
 * changing this controller. See README "MCP setup" for callers.
 */
class McpController extends Controller
{
    public function __construct(private readonly McpToolService $tools)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'tools' => array_keys(McpToolService::abilities()),
        ]);
    }

    public function __invoke(Request $request, string $tool): JsonResponse
    {
        $abilities = McpToolService::abilities();

        if (! array_key_exists($tool, $abilities)) {
            return response()->json(['error' => "Unknown tool [{$tool}]."], 404);
        }

        $token = $request->user()->currentAccessToken();
        $ability = $abilities[$tool];

        if ($token && method_exists($token, 'can') && ! $token->can($ability)) {
            return response()->json(['error' => "This token does not have the [{$ability}] ability."], 403);
        }

        if (! method_exists($this->tools, $tool)) {
            return response()->json(['error' => "Tool [{$tool}] is not implemented."], 501);
        }

        try {
            $result = $this->tools->{$tool}($request->user(), (array) $request->input());

            return response()->json(['tool' => $tool, 'result' => $result]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        } catch (HttpException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
