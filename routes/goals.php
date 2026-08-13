<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoalActivityController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\GoalMilestoneController;
use App\Http\Controllers\GoalTopicController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('goals', GoalController::class);
    Route::post('goals/{goal}/pause', [GoalController::class, 'pause'])->name('goals.pause');
    Route::post('goals/{goal}/resume', [GoalController::class, 'resume'])->name('goals.resume');
    Route::post('goals/{goal}/complete', [GoalController::class, 'complete'])->name('goals.complete');
    Route::post('goals/{goal}/archive', [GoalController::class, 'archive'])->name('goals.archive');

    Route::post('goals/{goal}/topics', [GoalTopicController::class, 'store'])->name('goals.topics.store');
    Route::post('goals/{goal}/topics/{topic}/complete', [GoalTopicController::class, 'complete'])->name('goals.topics.complete');
    Route::delete('goals/{goal}/topics/{topic}', [GoalTopicController::class, 'destroy'])->name('goals.topics.destroy');

    Route::post('goals/{goal}/milestones', [GoalMilestoneController::class, 'store'])->name('goals.milestones.store');
    Route::post('goals/{goal}/milestones/{milestone}/complete', [GoalMilestoneController::class, 'complete'])->name('goals.milestones.complete');
    Route::delete('goals/{goal}/milestones/{milestone}', [GoalMilestoneController::class, 'destroy'])->name('goals.milestones.destroy');

    Route::post('goals/{goal}/activities', [GoalActivityController::class, 'store'])->name('goals.activities.store');
    Route::delete('goals/{goal}/activities/{activity}', [GoalActivityController::class, 'destroy'])->name('goals.activities.destroy');
});
