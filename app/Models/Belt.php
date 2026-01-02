<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Belt extends Model
{
    protected $table = 'belt';
    protected $primaryKey = 'belt_id';
    public $incrementing = true;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the students for the belt.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'belt_id');
    }
}
