<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $table = 'exam';
    protected $primaryKey = 'exam_id';
    public $incrementing = true;

    protected $fillable = [
        'name',
        'date',
        'belt_id',
        'fees',
        'description',
        'active',
    ];

    protected $casts = [
        'date' => 'date',
        'fees' => 'decimal:2',
        'active' => 'boolean',
    ];
}
