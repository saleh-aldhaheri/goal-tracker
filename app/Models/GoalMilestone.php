<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalMilestone extends Model
{
    /** @use HasFactory<\Database\Factories\GoalMilestoneFactory> */
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'name',
        'description',
        'status',
        'progress',
        'due_date',
        'completed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed', 'progress' => 100, 'completed_at' => now()]);
    }
}
