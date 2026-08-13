<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets the user issue and revoke their own MCP/API tokens (spec section
 * 39). Tokens are Sanctum personal access tokens scoped to the abilities
 * the user selects; the plaintext value is only ever shown once.
 */
class ApiTokenController extends Controller
{
    protected const ALLOWED_ABILITIES = [
        'goals:read', 'goals:write', 'activities:read', 'activities:write', 'dashboard:read',
    ];

    public function index(Request $request): View
    {
        return view('settings.tokens', [
            'tokens' => $request->user()->tokens()->latest()->get(),
            'abilities' => self::ALLOWED_ABILITIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', 'in:'.implode(',', self::ALLOWED_ABILITIES)],
        ]);

        $token = $request->user()->createToken($validated['name'], $validated['abilities']);

        return redirect()->route('settings.tokens')
            ->with('status', 'Token created.')
            ->with('plainTextToken', $token->plainTextToken);
    }

    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return redirect()->route('settings.tokens')->with('status', 'Token revoked.');
    }
}
