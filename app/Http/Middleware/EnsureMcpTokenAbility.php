<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces token scopes (spec section 39). A session-authenticated web
 * request (no API token) is always allowed through, since first-party
 * cookie sessions are implicitly full-access for that user; only Sanctum
 * personal access tokens are scope-checked.
 */
class EnsureMcpTokenAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ($token && method_exists($token, 'can') && ! $token->can($ability)) {
            abort(403, "This token does not have the [{$ability}] ability.");
        }

        return $next($request);
    }
}
