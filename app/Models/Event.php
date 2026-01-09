<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $table = 'event';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the likes for the event.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(EventLike::class, 'event_id', 'event_id');
    }

    /**
     * Get the active likes (is_liked = true) for the event.
     */
    public function activeLikes(): HasMany
    {
        return $this->hasMany(EventLike::class, 'event_id', 'event_id')
            ->where('is_liked', true);
    }

    /**
     * Get the comments for the event.
     */
    public function eventComments(): HasMany
    {
        return $this->hasMany(EventComment::class, 'event_id', 'event_id')->where('is_active', true);
    }

    protected $primaryKey = 'event_id';
    public $timestamps = false; // Legacy table might not have created_at/updated_at

    protected $fillable = [
        'name',
        'from_date',
        'to_date',
        'venue',
        'type',
        'description',
        'fees',
        'fees_due_date',
        'penalty',
        'penalty_due_date',
        'isPublished',
        'active', // Assuming this exists or we use isPublished
        'category_id',
        'image',
        'subtitle',
        'likes',
        'comments',
        'shares',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'fees_due_date' => 'date',
        'penalty_due_date' => 'date',
        'fees' => 'decimal:2',
        'penalty' => 'decimal:2',
        'active' => 'boolean',
        'isPublished' => 'boolean',
        'likes' => 'integer',
        'comments' => 'integer',
        'shares' => 'integer',
    ];

    protected $appends = ['time_ago', 'is_liked'];

    public function getTimeAgoAttribute()
    {
        // Simple human diff logic, or use standard Carbon diffForHumans
        return $this->from_date ? \Carbon\Carbon::parse($this->from_date)->diffForHumans() : '';
    }

    public function getIsLikedAttribute()
    {
        // This will be overridden in EventResource with actual user data
        return false;
    }

    /**
     * Get the full URL for the image.
     */
    public function getImageAttribute($value)
    {
        $default = asset('images/default_event.png');

        if (empty($value)) {
            return $default;
        }

        // If it's already a full URL, return it
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Check if file exists in public directory
        if (!file_exists(public_path($value))) {
            return $default;
        }

        return asset($value);
    }

    // Accessors for API consistency
    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    public function getEventStartDatetimeAttribute()
    {
        return $this->from_date;
    }

    public function setEventStartDatetimeAttribute($value)
    {
        $this->attributes['from_date'] = $value;
    }

    public function getEventEndDatetimeAttribute()
    {
        return $this->to_date;
    }

    public function setEventEndDatetimeAttribute($value)
    {
        $this->attributes['to_date'] = $value;
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('from_date', '>=', now())
            ->orderBy('from_date', 'asc');
    }

    public function scopePublished($query)
    {
        return $query->where('isPublished', 1);
    }
}
