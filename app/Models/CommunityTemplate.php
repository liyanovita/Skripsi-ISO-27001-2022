<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityTemplate extends Model
{
    protected $fillable = [
        'user_id', 
        'title', 
        'description', 
        'content',
        'author_name', 
        'tags', 
        'base_score', 
        'content_data',
        'format',
        'size',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'downloads_count',
        'upvotes',
        'rating_sum',
        'rating_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'content_data' => 'array',
        'base_score' => 'float',
        'downloads_count' => 'integer',
        'upvotes' => 'integer',
        'rating_sum' => 'integer',
        'rating_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this template
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get average star rating (1-5)
     */
    public function getAvgRatingAttribute(): float
    {
        if ($this->rating_count === 0) return 0;
        return round($this->rating_sum / $this->rating_count, 1);
    }

    /**
     * Scope: Get most popular templates
     */
    public function scopePopular($query)
    {
        return $query->orderByDesc('downloads_count')
            ->orderByDesc('upvotes');
    }

    /**
     * Scope: Get highest rated templates
     */
    public function scopeTopRated($query)
    {
        return $query->where('rating_count', '>', 0)
            ->orderByRaw('(rating_sum / rating_count) DESC');
    }

    /**
     * Scope: Get templates by search term
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('title', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->orWhere('author_name', 'like', "%{$term}%");
    }
}
