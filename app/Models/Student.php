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
    public $timestamps = false;

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
        'profile_img',
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
    /**
     * Get the student's full name.
     */
    public function getNameAttribute(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Get the student's GR Number (Dynamic).
     * Format: STU-{Year}-{ID}
     */
    public function getGrNoAttribute(): string
    {
        $year = $this->doj ? $this->doj->format('Y') : date('Y'); // Fallback to current year if DOJ missing (though it shouldn't be)
        return "STU-{$year}-{$this->student_id}";
    }

    protected $appends = ['name', 'gr_no'];
}
