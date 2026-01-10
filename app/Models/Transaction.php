<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transaction model - Mapped to legacy 'transcation' table (intentional typo for backward compatibility)
 */
class Transaction extends Model
{
    /**
     * The table associated with the model.
     * Note: Table name has a typo in legacy DB - maintaining for backward compatibility.
     */
    protected $table = 'transcation';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'transcation_id';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'order_id',
        'status',
        'type',
        'ref_id',
        'amount',
        'date',
        'months',
        'year',
        'coupon_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Status constants matching legacy behavior
     */
    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_FAILED = -1;

    /**
     * Type constants
     */
    const TYPE_FEES = 'fees';
    const TYPE_PRODUCT = 'product';
    const TYPE_EXAM = 'exam';
    const TYPE_EVENT = 'event';

    /**
     * Get the student associated with this transaction.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the coupon associated with this transaction.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    /**
     * Scope to filter by pending status.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter by completed status.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
