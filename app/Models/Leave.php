<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $table = 'leave_table';
    protected $primaryKey = 'leave_id';
    public $incrementing = true;
    public $timestamps = false; // Legacy table

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 5;
    const STATUS_REJECTED = 10;

    protected $fillable = [
        'student_id',
        'from_date',
        'to_date',
        'reason',
        'subject',
        'status',
        'applied_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'applied_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the student that owns the leave.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the reviewer (instructor/admin).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }

    /**
     * Get human-readable status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Pending',
        };
    }

    /**
     * Calculate number of leave days.
     */
    public function getLeaveDaysAttribute(): int
    {
        if (!$this->from_date || !$this->to_date) {
            return 0;
        }
        return $this->from_date->diffInDays($this->to_date) + 1;
    }
}
