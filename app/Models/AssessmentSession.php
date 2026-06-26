<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSession extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'name', 'status', 'overall_maturity_score', 'ai_summary'];

    protected $casts = [
        'overall_maturity_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns this session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all assessment results for this session
     */
    public function results(): HasMany
    {
        return $this->hasMany(AssessmentResult::class, 'session_id');
    }

    /**
     * Calculate and update the overall maturity score
     * Only includes completed results
     */
    public function calculateMaturityScore(): float
    {
        $avg = $this->results()
            ->where('status', 'completed')
            ->where('maturity_rating', '>=', 0)
            ->avg('maturity_rating') ?? 0;
        
        $this->update(['overall_maturity_score' => round($avg, 2)]);
        return $avg;
    }

    /**
     * Scope: Get only completed sessions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Get only in-progress sessions
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope: Get sessions for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}