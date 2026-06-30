<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'category',
        'description',
        'content',
        'format',
        'size',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'icon',
        'is_system',
        'downloads_count',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_system' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the custom resource.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get only system resources
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope: Get only user-created resources
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope: Get resources by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Search resources
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('title', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->orWhere('content', 'like', "%{$term}%");
    }

    /**
     * Get the icon attribute with style prefix normalized.
     */
    public function getIconAttribute($value)
    {
        $iconClass = $value ?: 'fa-file-lines';
        if (!preg_match('/\b(fa-solid|fa-regular|fa-brands|fa-light|fa-duotone|fa-thin)\b/', $iconClass)) {
            if (strpos($iconClass, 'fa-') !== 0) {
                $iconClass = 'fa-' . $iconClass;
            }
            $iconClass = 'fa-solid ' . $iconClass;
        }
        return $iconClass;
    }

    /**
     * Check if the content is written in HTML format.
     */
    public function isHtml(): bool
    {
        $tags = ['<p>', '<div>', '<strong>', '<em>', '<ul>', '<ol>', '<table>', '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>', '<br>', '<span'];
        foreach ($tags as $tag) {
            if (str_contains((string)$this->content, $tag)) {
                return true;
            }
        }
        return false;
    }
}

