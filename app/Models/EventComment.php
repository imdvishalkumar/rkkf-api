<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventComment extends Model
{
    use HasFactory;

    protected $table = 'event_comments';

    protected $fillable = [
        'event_id',
        'user_id',
        'parent_id',
        'reply_to_user_id',
        'comment',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['total_likes', 'replies_count', 'is_liked'];

    /**
     * Get the event that owns the comment.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    /**
     * Get the user that wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the user being replied to.
     */
    public function replyToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reply_to_user_id', 'user_id');
    }

    /**
     * Get the parent comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(EventComment::class, 'parent_id');
    }

    /**
     * Get the replies for the comment.
     */
    public function replies(): HasMany
    {
        // Only active replies
        return $this->hasMany(EventComment::class, 'parent_id')->where('is_active', true);
    }

    /**
     * Get all replies regardless of status (for admin/reference)
     */
    public function allReplies(): HasMany
    {
        return $this->hasMany(EventComment::class, 'parent_id');
    }

    /**
     * Get the likes for the comment.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(EventCommentLike::class, 'event_comment_id');
    }

    /**
     * Scope a query to only include active comments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include parent comments (no parent_id).
     */
    public function scopeParentComments($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Attribute: Total Likes Count
     */
    public function getTotalLikesAttribute()
    {
        return $this->likes()->count();
    }

    /**
     * Attribute: Replies Count
     */
    public function getRepliesCountAttribute()
    {
        return $this->replies()->count();
    }

    /**
     * Attribute: Is Liked by Auth User
     * This needs to be set manually or via a separate query usually, 
     * but we can try to access authenticated user if available.
     * However, for APIs, it's better to load this via loading the relation or attribute.
     * Returning false by default, will be populated by resource/controller.
     */
    public function getIsLikedAttribute()
    {
        // If we have the 'likes' relation loaded checking for auth user
        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', auth()->id());
        }
        return false;
    }
}
