<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AssessmentSession extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'organization_id', 'name', 'status', 'overall_maturity_score', 'ai_summary', 'ai_summary_hash', 'deadline'];

    protected $casts = [
        'overall_maturity_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'deadline' => 'date',
    ];

    /**
     * Get the user that owns this session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization associated with this session
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all assessment results for this session
     */
    public function results(): HasMany
    {
        return $this->hasMany(AssessmentResult::class, 'session_id');
    }

    /**
     * Get all users invited to this session (via pivot table)
     */
    public function invitedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assessment_session_users', 'session_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Check if a given user has access to this session
     */
    public function hasUserAccess(int $userId): bool
    {
        return $this->user_id === $userId ||
               $this->invitedUsers()->where('user_id', $userId)->exists();
    }

    /**
     * Calculate and update the overall maturity score.
     * Implements Equation (3.1): only Applicable controls with questions are counted.
     * This is consistent with ResultService::updateSessionScore().
     *
     * Si = maturity_rating of each Applicable control
     * n  = count of Applicable controls
     */
    public function calculateMaturityScore(): float
    {
        $avg = $this->results()
            ->where('status', 'completed')
            ->where('is_applicable', true)
            ->whereHas('standard', function ($query) {
                $query->whereNotNull('questions')
                      ->where('questions', '!=', '[]')
                      ->where('questions', '!=', 'null');
            })
            ->avg('maturity_rating') ?? 0;

        $this->update(['overall_maturity_score' => round($avg, 2)]);
        return (float) $avg;
    }

    /**
     * Get session-level Compliance Score based on thesis Equation (3.1):
     * Compliance Score = (Σ Si) / (n × Smax) × 100%
     *
     * Mathematically equivalent to: (average maturity / 5) × 100
     * Returns integer percentage 0–100.
     */
    public function getComplianceScoreAttribute(): int
    {
        return (int) round(($this->overall_maturity_score / 5) * 100);
    }

    /**
     * Get session-level Compliance Status based on thesis Tabel 3.4:
     * - Compliant          : score >= 4.0  (80–100%)
     * - Partially Compliant: score >= 2.0  (40–79%)
     * - Non-Compliant      : score <  2.0  (0–39%)
     */
    public function getComplianceStatusAttribute(): string
    {
        if ($this->overall_maturity_score >= 4.0) return 'Compliant';
        if ($this->overall_maturity_score >= 2.0) return 'Partially Compliant';
        return 'Non-Compliant';
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