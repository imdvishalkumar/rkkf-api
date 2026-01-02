<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'event';
    protected $primaryKey = 'event_id';
    public $incrementing = true;

    protected $fillable = [
        'name',
        'date',
        'description',
        'fees',
        'active',
    ];

    protected $casts = [
        'date' => 'date',
        'fees' => 'decimal:2',
        'active' => 'boolean',
    ];
}
