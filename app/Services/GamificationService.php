<?php

namespace App\Services;

use App\Models\User;

/**
 * The light gamification layer for the Homestead dashboard. XP is derived
 * strictly from real activity — nothing is stored or manually adjustable:
 *
 *   +1 XP per minute logged, +20 per completed topic, +50 per completed
 *   milestone, +200 per completed goal, +10 per day of current streak.
 *
 * Gold is simply lifetime XP (a fun counter), and level rises on an
 * increasing curve so each level takes a little more work than the last.
 */
class GamificationService
{
    public function __construct(private readonly StreakService $streaks)
    {
    }

    public function stats(User $user): array
    {
        $minutes = (int) $user->goals()
            ->join('goal_activities', 'goals.id', '=', 'goal_activities.goal_id')
            ->sum('goal_activities.duration_minutes');

        $topics = (int) $user->goals()
            ->join('goal_topics', 'goals.id', '=', 'goal_topics.goal_id')
            ->where('goal_topics.status', 'completed')
            ->count('goal_topics.id');

        $milestones = (int) $user->goals()
            ->join('goal_milestones', 'goals.id', '=', 'goal_milestones.goal_id')
            ->where('goal_milestones.status', 'completed')
            ->count('goal_milestones.id');

        $goalsDone = (int) $user->goals()->where('status', 'completed')->count();

        $streak = $user->goals()
            ->get()
            ->max(fn ($goal) => $this->streaks->currentStreak($goal)) ?: 0;

        $xp = $minutes + ($topics * 20) + ($milestones * 50) + ($goalsDone * 200) + ($streak * 10);

        [$level, $into, $needed] = $this->levelFor($xp);

        return [
            'xp' => $xp,
            'gold' => $xp,
            'level' => $level,
            'level_into' => $into,
            'level_needed' => $needed,
            'streak' => $streak,
            'achievements' => [
                ['name' => 'First Step', 'icon' => '🌱', 'desc' => 'Create a goal', 'unlocked' => $user->goals()->count() >= 1],
                ['name' => 'On Fire', 'icon' => '🔥', 'desc' => '7-day streak', 'unlocked' => $streak >= 7],
                ['name' => 'Harvest', 'icon' => '🌾', 'desc' => 'Complete 5 goals', 'unlocked' => $goalsDone >= 5],
                ['name' => 'Scholar', 'icon' => '📚', 'desc' => '20 topics done', 'unlocked' => $topics >= 20],
                ['name' => 'Builder', 'icon' => '⚒️', 'desc' => '10 milestones', 'unlocked' => $milestones >= 10],
                ['name' => 'Farmer', 'icon' => '👑', 'desc' => 'Reach level 20', 'unlocked' => $level >= 20],
            ],
        ];
    }

    /** @return array{0:int,1:int,2:int} level, xp-into-level, xp-needed-for-next */
    protected function levelFor(int $xp): array
    {
        $level = 1;
        $remaining = $xp;
        $need = 200;

        while ($remaining >= $need) {
            $remaining -= $need;
            $level++;
            $need += 50;
        }

        return [$level, $remaining, $need];
    }
}
