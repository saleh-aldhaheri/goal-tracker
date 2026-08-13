<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GoalActivityController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\GoalTopicController;
use App\Http\Controllers\Api\McpController;
use Illuminate\Support\Facades\Route;

// All API endpoints require an authenticated Sanctum token (spec sections
// 39 and 71 — no unauthenticated read or write access). Each route is
// additionally gated by a token ability/scope via the mcp.ability middleware.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('goals', [GoalController::class, 'index'])->middleware('mcp.ability:goals:read');
    Route::get('goals/{goal}', [GoalController::class, 'show'])->middleware('mcp.ability:goals:read');
    Route::post('goals', [GoalController::class, 'store'])->middleware('mcp.ability:goals:write');
    Route::match(['put', 'patch'], 'goals/{goal}', [GoalController::class, 'update'])->middleware('mcp.ability:goals:write');
    Route::delete('goals/{goal}', [GoalController::class, 'destroy'])->middleware('mcp.ability:goals:write');

    Route::get('goals/{goal}/topics', [GoalTopicController::class, 'index'])->middleware('mcp.ability:goals:read');
    Route::post('goals/{goal}/topics', [GoalTopicController::class, 'store'])->middleware('mcp.ability:goals:write');

    Route::get('goals/{goal}/activities', [GoalActivityController::class, 'index'])->middleware('mcp.ability:activities:read');
    Route::post('goals/{goal}/activities', [GoalActivityController::class, 'store'])->middleware('mcp.ability:activities:write');

    Route::get('dashboard', [DashboardController::class, 'index'])->middleware('mcp.ability:dashboard:read');
    Route::get('goals/{goal}/dashboard', [DashboardController::class, 'goalDashboard'])->middleware('mcp.ability:dashboard:read');

    // MCP-compatible tool-call endpoint. Ability is checked per-tool inside
    // the controller since each tool needs a different scope. See README
    // "MCP setup" for the full tool list and spec section 38.
    Route::post('mcp/tools/{tool}', McpController::class)->name('mcp.tools.invoke');
    Route::get('mcp/tools', [McpController::class, 'index'])->name('mcp.tools.index');
});
