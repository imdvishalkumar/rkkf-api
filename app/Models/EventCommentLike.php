<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCommentLike extends Model
{
    use HasFactory;

    protected $table = 'event_comment_likes';
    public $timestamps = false; // We only have created_at, handled by migration 'useCurrent' or manual

    protected $fillable = [
        'event_comment_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the comment that was liked.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(EventComment::class, 'event_comment_id');
    }

    /**
     * Get the user who liked the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
