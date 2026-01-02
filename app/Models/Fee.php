<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fee extends Model
{
    protected $table = 'fees';
    protected $primaryKey = 'fee_id';
    public $incrementing = true;

    protected $fillable = [
        'student_id',
        'months',
        'year',
        'date',
        'amount',
        'coupon_id',
        'additional',
        'disabled',
        'mode',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'additional' => 'boolean',
        'disabled' => 'boolean',
    ];

    /**
     * Get the student that owns the fee.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the coupon for the fee.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
}
