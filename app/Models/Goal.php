<?php

namespace App\Models;

use App\Enums\GoalPriority;
use App\Enums\GoalStatus;
use App\Enums\GoalType;
use App\Enums\TrackingMode;
use App\Services\GoalProgressService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    /** @use HasFactory<\Database\Factories\GoalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'type',
        'status',
        'priority',
        'tracking_mode',
        'start_date',
        'target_date',
        'completed_at',
        'color',
        'icon',
        'target_value',
        'target_unit',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'type' => GoalType::class,
            'status' => GoalStatus::class,
            'priority' => GoalPriority::class,
            'tracking_mode' => TrackingMode::class,
            'start_date' => 'date',
            'target_date' => 'date',
            'completed_at' => 'datetime',
            'target_value' => 'decimal:2',
            'settings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(GoalTopic::class)->orderBy('sort_order');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(GoalMilestone::class)->orderBy('sort_order');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(GoalActivity::class)->orderByDesc('occurred_at');
    }

    /** Progress percentage (0-100), derived from tracking_mode. Section 52-53. */
    public function progress(): int
    {
        return app(GoalProgressService::class)->calculate($this);
    }

    public function totalMinutesSpent(): int
    {
        return (int) $this->activities()->sum('duration_minutes');
    }
}
