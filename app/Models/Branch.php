<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $table = 'branch';
    protected $primaryKey = 'branch_id';
    public $incrementing = true;

    protected $fillable = [
        'name',
        'days',
        'fees',
        'late',
        'discount',
    ];

    /**
     * Get the students for the branch.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'branch_id');
    }
}
