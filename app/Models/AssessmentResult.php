<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentResult extends Model
{
    protected $fillable = [
        'session_id', 'iso_standard_id', 'answers', 'maturity_rating', 
        'notes', 'evidence_file', 'status', 'ai_recommendation',
        'corrective_action_plan', 'risk_priority', 'control_insight',
        'evidence_validation', 'is_applicable', 'soa_justification', 'implementation_status',
        'treatment_due_date', 'treatment_pic', 'treatment_status',
    ];

    protected $casts = [
        'answers' => 'array',
        'corrective_action_plan' => 'array',
        'control_insight' => 'array',
        'is_applicable' => 'boolean',
        'treatment_due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saved(function ($result) {
            $result->session->calculateMaturityScore();
        });

        static::deleted(function ($result) {
            $result->session->calculateMaturityScore();
        });

        static::updated(function ($result) {
            $trackedFields = [
                'maturity_rating', 'is_applicable', 'implementation_status',
                'treatment_due_date', 'treatment_pic', 'treatment_status'
            ];
            
            foreach ($trackedFields as $field) {
                if ($result->wasChanged($field)) {
                    AuditTrail::create([
                        'user_id' => auth()->id(),
                        'model_type' => get_class($result),
                        'model_id' => $result->id,
                        'action' => 'updated',
                        'field_changed' => $field,
                        'old_value' => $result->getOriginal($field),
                        'new_value' => $result->$field,
                    ]);
                }
            }
        });
    }

    /**
     * Get the ISO standard for this result
     */
    public function standard(): BelongsTo
    {
        return $this->belongsTo(IsoStandard::class, 'iso_standard_id');
    }

    /**
     * Get the assessment session for this result
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AssessmentSession::class, 'session_id');
    }

    /**
     * Get compliance status based on maturity rating
     * Compliant: >= 4, Partially Compliant: >= 2, Non-Compliant: < 2
     */
    public function getComplianceStatusAttribute(): string
    {
        if ($this->maturity_rating >= 4) return 'Compliant';
        if ($this->maturity_rating >= 2) return 'Partially Compliant';
        return 'Non-Compliant';
    }

    /**
     * Get risk level based on maturity rating
     */
    public function getRiskLevelAttribute(): string
    {
        return match((int)$this->maturity_rating) {
            0 => 'Unassessed',
            1 => 'Critical',
            2 => 'High',
            3 => 'Medium',
            4 => 'Low',
            5 => 'Low',
            default => 'Unassessed'
        };
    }

    /**
     * Scope: Get results that are not compliant
     */
    public function scopeNonCompliant($query)
    {
        return $query->where('maturity_rating', '<', 4)
            ->where('maturity_rating', '>', 0);
    }

    /**
     * Scope: Get results that are overdue
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('treatment_due_date')
            ->where('treatment_due_date', '<', now()->toDateString());
    }

    /**
     * Scope: Get results with pending treatment
     */
    public function scopePendingTreatment($query)
    {
        return $query->whereIn('treatment_status', ['open', 'in_progress']);
    }
}
