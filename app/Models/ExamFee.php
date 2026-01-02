<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamFee extends Model
{
    protected $table = 'exam_fees';
    protected $primaryKey = 'exam_fees_id';
    public $incrementing = true;

    protected $fillable = [
        'exam_id',
        'student_id',
        'date',
        'mode',
        'rp_order_id',
        'status',
        'amount',
        'exam_belt_id',
        'up',
        'dump',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'integer',
        'amount' => 'decimal:2',
        'up' => 'integer',
        'dump' => 'integer',
    ];

    /**
     * Get the exam that owns the fee.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    /**
     * Get the student that owns the fee.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the belt for the exam.
     */
    public function examBelt(): BelongsTo
    {
        return $this->belongsTo(Belt::class, 'exam_belt_id', 'belt_id');
    }
}


