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
    public function hasUserAccess(User|int|null $user = null): bool
    {
        if ($user instanceof User) {
            $u = $user;
        } elseif (is_int($user)) {
            $u = User::find($user);
        } else {
            $u = auth()->user();
        }

        if (!$u) return false;

        if ($u->isAdmin()) return true;
        if ($this->user_id === $u->id) return true;
        if ($this->organization_id && $u->organization_id && $this->organization_id === $u->organization_id) return true;

        return $this->invitedUsers()->where('assessment_session_users.user_id', $u->id)->exists();
    }

    /**
     * Check if session is locked/read-only for a user:
     * - Admins are never locked out.
     * - Non-admins are locked if status is completed/closed OR if deadline is past.
     */
    /**
     * Check if session is locked/read-only for a user:
     * - Admins are never locked out.
     * - Non-admins are locked if status is completed/closed OR if deadline is past.
     */
    public function isLockedForUser(?User $user = null): bool
    {
        if (in_array($this->status, ['completed', 'closed'])) {
            return true;
        }

        if ($this->deadline && $this->deadline->endOfDay()->isPast()) {
            $user = $user ?? auth()->user();
            if ($user && ($user->isAdmin() || $user->role === 'admin')) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Check if session deadline is past.
     */
    public function isPastDeadline(): bool
    {
        return $this->deadline && $this->deadline->endOfDay()->isPast();
    }

    /**
     * Get user-friendly lock banner message when session is locked in read-only mode.
     */
    public function getLockReason(?User $user = null): ?string
    {
        if (!$this->isLockedForUser($user)) {
            return null;
        }

        if ($this->isPastDeadline()) {
            $formattedDate = $this->deadline ? $this->deadline->format('d M Y H:i') : '';
            return __('Audit Deadline Passed (Read-Only Mode): The deadline for this audit session has expired (:date). All inputs are now locked in read-only mode for regular users. Please contact an administrator if you need to extend the deadline or reopen this session.', ['date' => $formattedDate]);
        }

        if (in_array($this->status, ['completed', 'closed'])) {
            return __('Audit Session Finalized (Read-Only Mode): This audit session has been completed and locked. Data and control entries are locked to maintain audit trail integrity unless an administrator reopens the session.');
        }

        return __('This audit session is currently locked in read-only mode.');
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
    public function getComplianceScoreAttribute(): float
    {
        return round(($this->overall_maturity_score / 5) * 100, 1);
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
     * Get Maturity Level Info based on research classification table:
     * 0.00 – 0.49: Level 0 – Non-existent
     * 0.50 – 1.49: Level 1 – Initial
     * 1.50 – 2.49: Level 2 – Limited/Repeatable
     * 2.50 – 3.49: Level 3 – Defined
     * 3.50 – 4.49: Level 4 – Managed
     * 4.50 – 5.00: Level 5 – Optimized
     */
    public static function getMaturityLevelClassification(float $score): array
    {
        if ($score >= 4.50) {
            return [
                'level'       => 5,
                'name'        => 'Maturity: Level 5 (Optimized)',
                'title'       => 'Optimized',
                'description' => 'Kontrol telah diterapkan secara optimal serta didukung oleh proses evaluasi dan peningkatan berkelanjutan.',
                'badge_color' => 'bg-blue-50 text-blue-700 border-blue-200',
            ];
        }
        if ($score >= 3.50) {
            return [
                'level'       => 4,
                'name'        => 'Maturity: Level 4 (Managed)',
                'title'       => 'Managed',
                'description' => 'Implementasi kontrol telah dipantau dan dievaluasi menggunakan indikator yang terukur.',
                'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ];
        }
        if ($score >= 2.50) {
            return [
                'level'       => 3,
                'name'        => 'Maturity: Level 3 (Defined)',
                'title'       => 'Defined',
                'description' => 'Kontrol telah diterapkan sesuai prosedur yang terdokumentasi dan dilaksanakan secara konsisten.',
                'badge_color' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            ];
        }
        if ($score >= 1.50) {
            return [
                'level'       => 2,
                'name'        => 'Maturity: Level 2 (Limited/Repeatable)',
                'title'       => 'Limited/Repeatable',
                'description' => 'Kontrol mulai diterapkan dan dikelola, namun belum diterapkan secara menyeluruh.',
                'badge_color' => 'bg-orange-50 text-orange-700 border-orange-200',
            ];
        }
        if ($score >= 0.50) {
            return [
                'level'       => 1,
                'name'        => 'Maturity: Level 1 (Initial)',
                'title'       => 'Initial',
                'description' => 'Implementasi masih bersifat awal, belum terdokumentasi, dan belum dilakukan secara konsisten.',
                'badge_color' => 'bg-rose-50 text-rose-700 border-rose-200',
            ];
        }
        return [
            'level'       => 0,
            'name'        => 'Maturity: Level 0 (Non-existent)',
            'title'       => 'Non-existent',
            'description' => 'Kontrol belum diterapkan atau belum terdapat implementasi yang dapat dibuktikan.',
            'badge_color' => 'bg-slate-100 text-slate-600 border-slate-200',
        ];
    }

    public function getMaturityLevelAttribute(): array
    {
        return self::getMaturityLevelClassification((float)$this->overall_maturity_score);
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