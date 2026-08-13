<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalTopic extends Model
{
    /** @use HasFactory<\Database\Factories\GoalTopicFactory> */
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'name',
        'description',
        'status',
        'target_value',
        'completed_value',
        'sort_order',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'completed_value' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed', 'completed_at' => now()]);
    }
}
