<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    /**
     * Scope a query to only include active records.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', 1);
    }

    /**
     * Scope a query to only include inactive records.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', 0);
    }

    /**
     * Check if the model is active.
     */
    public function isActive(): bool
    {
        return $this->active == 1;
    }

    /**
     * Check if the model is inactive.
     */
    public function isInactive(): bool
    {
        return $this->active == 0;
    }

    /**
     * Activate the model.
     */
    public function activate(): bool
    {
        return $this->update(['active' => 1]);
    }

    /**
     * Deactivate the model.
     */
    public function deactivate(): bool
    {
        return $this->update(['active' => 0]);
    }
}



