<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFee extends Model
{
    protected $table = 'event_fees';
    protected $primaryKey = 'event_fees_id';
    public $incrementing = true;

    protected $fillable = [
        'event_id',
        'student_id',
        'date',
        'mode',
        'rp_order_id',
        'status',
        'amount',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'integer',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the event that owns the fee.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    /**
     * Get the student that owns the fee.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}


