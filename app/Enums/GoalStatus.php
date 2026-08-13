<?php

namespace App\Enums;

enum GoalStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Paused = 'paused';
    case Archived = 'archived';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
