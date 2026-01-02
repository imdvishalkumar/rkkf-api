<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'student_id';
    public $incrementing = true;

    protected $fillable = [
        'firstname',
        'lastname',
        'gender',
        'email',
        'password',
        'belt_id',
        'dadno',
        'dadwp',
        'momno',
        'momwp',
        'selfno',
        'selfwp',
        'dob',
        'doj',
        'address',
        'branch_id',
        'pincode',
        'active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'dob' => 'date',
        'doj' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Get the branch that the student belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get the belt that the student has.
     */
    public function belt(): BelongsTo
    {
        return $this->belongsTo(Belt::class, 'belt_id');
    }

    /**
     * Get the fees for the student.
     */
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'student_id');
    }

    /**
     * Get the student's full name.
     */
    public function getNameAttribute(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}
