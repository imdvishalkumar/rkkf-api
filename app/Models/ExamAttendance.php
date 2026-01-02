<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttendance extends Model
{
    protected $table = 'exam_attendance';
    protected $primaryKey = 'exam_attendance_id';
    public $incrementing = true;

    protected $fillable = [
        'exam_id',
        'student_id',
        'attend',
        'user_id',
        'certificate_no',
    ];

    /**
     * Get the exam that owns the attendance.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    /**
     * Get the student that owns the attendance.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the user who marked the attendance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}


