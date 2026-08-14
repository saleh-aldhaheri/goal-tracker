<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FarmController extends Controller
{
    public function __invoke(Request $request): View
    {
        $goals = $request->user()->goals()
            ->withMax('activities', 'occurred_at')
            ->orderBy('name')
            ->get()
            ->map(fn (Goal $goal) => [
                'id' => $goal->id,
                'name' => $goal->name,
                'progress' => $goal->progress(),
                'color' => $goal->color ?: $this->colorFor($goal->type->value),
                'last_days' => $goal->activities_max_occurred_at
                    ? max(0, (int) Carbon::parse($goal->activities_max_occurred_at)->diffInDays(now()))
                    : null,
            ]);

        return view('farm', ['goals' => $goals]);
    }

    protected function colorFor(string $type): string
    {
        return match ($type) {
            'study' => '#9db4ff',
            'project' => '#ffd166',
            'habit', 'fitness' => '#9be0c0',
            'recurring' => '#c39bff',
            default => '#ff9db5',
        };
    }
}
