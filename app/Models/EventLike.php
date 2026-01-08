<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLike extends Model
{
    protected $table = 'event_likes';

    protected $fillable = [
        'user_id',
        'event_id',
        'is_liked',
    ];

    protected $casts = [
        'is_liked' => 'boolean',
    ];

    /**
     * Get the user that liked the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the event that was liked.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }
}
