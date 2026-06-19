<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IsoStandard extends Model
{
    protected $fillable = [
        'parent_id', 'type', 'level', 'code', 'title', 
        'description', 'questions', 'implementation_guidance'
    ];

    protected $casts = [
        'questions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get child standards (clauses or controls)
     */
    public function children(): HasMany
    {
        return $this->hasMany(IsoStandard::class, 'parent_id')
                    ->orderByRaw('LENGTH(code) ASC, code ASC');
    }

    /**
     * Get parent standard
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(IsoStandard::class, 'parent_id');
    }

    /**
     * Get all assessment results for this standard
     */
    public function results(): HasMany
    {
        return $this->hasMany(AssessmentResult::class, 'iso_standard_id');
    }

    /**
    public function scopeClauses($query)
    {
        return $query->whereIn('type', ['clause', 'clausa']);
    }

    /**
     * Scope: Get only controls
     */
    public function scopeControls($query)
    {
        return $query->where('type', 'control');
    }

    /**
     * Scope: Get root standards (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}