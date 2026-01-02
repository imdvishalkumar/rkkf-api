<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BranchGroup extends Model
{
    protected $table = 'branch_groups';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'branch_ids',
        'active',
    ];

    protected $casts = [
        'branch_ids' => 'array',
        'active' => 'boolean',
    ];

    /**
     * Get branches in this group
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(
            Branch::class,
            'branch_group_members',
            'branch_group_id',
            'branch_id',
            'id',
            'branch_id'
        );
    }

    /**
     * Get branch IDs as array
     */
    public function getBranchIdsAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }

    /**
     * Set branch IDs
     */
    public function setBranchIdsAttribute($value)
    {
        $this->attributes['branch_ids'] = is_array($value) 
            ? json_encode($value) 
            : $value;
    }

    /**
     * Check if branch is in this group
     */
    public function hasBranch(int $branchId): bool
    {
        $branchIds = $this->branch_ids;
        return in_array($branchId, $branchIds);
    }

    /**
     * Get branches by group name (from config)
     */
    public static function getBranchesFromConfig(string $groupName): array
    {
        return config("branch_groups.groups.{$groupName}", []);
    }

    /**
     * Scope for active groups
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}



