<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasBranchAccess
{
    /**
     * Scope a query to filter by branch access.
     * For users with specific branch access, filter results to their branch.
     */
    public function scopeWithBranchAccess(Builder $query): Builder
    {
        $user = Auth::user();
        
        if (!$user) {
            return $query;
        }

        // If user has a specific branch_id, filter by it
        // This can be extended based on your access control logic
        if (isset($user->branch_id) && $user->branch_id) {
            return $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }

    /**
     * Check if user has access to this branch.
     */
    public function hasBranchAccess(?int $branchId = null): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Admin users have access to all branches
        if ($user->role === 1) {
            return true;
        }

        // Check if user has access to specific branch
        $modelBranchId = $branchId ?? $this->branch_id ?? null;
        
        if (isset($user->branch_id) && $user->branch_id == $modelBranchId) {
            return true;
        }

        return false;
    }

    /**
     * Get accessible branch IDs for current user.
     */
    public static function getAccessibleBranchIds(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        // Admin users have access to all branches
        if ($user->role === 1) {
            return \App\Models\Branch::pluck('branch_id')->toArray();
        }

        // Return user's specific branch
        if (isset($user->branch_id) && $user->branch_id) {
            return [$user->branch_id];
        }

        return [];
    }
}



