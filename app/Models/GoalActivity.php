<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalActivity extends Model
{
    /** @use HasFactory<\Database\Factories\GoalActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'user_id',
        'topic_id',
        'milestone_id',
        'type',
        'value',
        'unit',
        'title',
        'description',
        'occurred_at',
        'duration_minutes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'occurred_at' => 'datetime',
            'duration_minutes' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(GoalTopic::class, 'topic_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(GoalMilestone::class, 'milestone_id');
    }
}
