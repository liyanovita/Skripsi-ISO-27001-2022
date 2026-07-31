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
        'evidence_validation', 'impact_interpretation', 'is_applicable', 'soa_justification', 'implementation_status',
        'treatment_due_date', 'treatment_pic', 'treatment_status', 'treatment_progress', 'evidence_after_improvement',
        'ai_data_hash',
    ];

    protected $casts = [
        'answers' => 'array',
        'corrective_action_plan' => 'array',
        'control_insight' => 'array',
        'evidence_file' => 'array',
        'evidence_after_improvement' => 'array',
        'is_applicable' => 'boolean',
        'treatment_due_date' => 'date',
        'treatment_progress' => 'integer',
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
                'treatment_due_date', 'treatment_pic', 'treatment_status',
                'treatment_progress', 'evidence_after_improvement'
            ];
            
            foreach ($trackedFields as $field) {
                if ($result->wasChanged($field)) {
                    $oldVal = $result->getOriginal($field);
                    $newVal = $result->$field;
                    AuditTrail::create([
                        'user_id' => auth()->id(),
                        'model_type' => get_class($result),
                        'model_id' => $result->id,
                        'action' => 'updated',
                        'field_changed' => $field,
                        'old_value' => is_array($oldVal) ? json_encode($oldVal) : (string)$oldVal,
                        'new_value' => is_array($newVal) ? json_encode($newVal) : (string)$newVal,
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
     * Get Gap value based on thesis formula: Gap = T - C
     * T = 5 (Target implementation)
     * C = maturity_rating (Actual self-assessment score)
     */
    public function getGapAttribute(): int
    {
        if ($this->maturity_rating === null) return 5;
        return max(0, 5 - (int)$this->maturity_rating);
    }

    /**
     * Get compliance status based on thesis decision matrix:
     * C = 5 (Gap = 0) -> Compliant
     * C = 3, 4 (Gap = 1, 2) -> Partially Compliant
     * C <= 2 (Gap >= 3) -> Non-Compliant
     */
    public function getComplianceStatusAttribute(): string
    {
        if (!$this->is_applicable) return 'Not Applicable';
        if ($this->maturity_rating === null) return 'Unassessed';
        $c = (int) $this->maturity_rating;
        if ($c >= 5) return 'Compliant';
        if ($c >= 3) return 'Partially Compliant';
        return 'Non-Compliant';
    }

    /**
     * Get risk priority level based on thesis decision matrix:
     * Non-Compliant & Gap >= 3 -> High
     * Non-Compliant & Gap < 3  -> Medium
     * Partially Compliant & Gap >= 2 -> Medium
     * Partially Compliant & Gap < 2 -> Low
     * Compliant (Semua nilai) -> Low
     */
    public function getCalculatedRiskPriorityAttribute(): string
    {
        if (!$this->is_applicable) return 'N/A';
        
        $status = $this->compliance_status;
        $gap = $this->gap;

        if ($status === 'Non-Compliant') {
            return $gap >= 3 ? 'High' : 'Medium';
        }
        if ($status === 'Partially Compliant') {
            return $gap >= 2 ? 'Medium' : 'Low';
        }
        return 'Low';
    }

    /**
     * Get risk level attribute (falls back to saved risk_priority or calculated_risk_priority)
     */
    public function getRiskLevelAttribute(): string
    {
        if (!$this->is_applicable) return 'N/A';
        if ($this->maturity_rating === null) return 'Unassessed';

        return $this->risk_priority ?: $this->calculated_risk_priority;
    }

    /**
     * Scope: Get results that are not compliant (maturity 0–3)
     */
    public function scopeNonCompliant($query)
    {
        return $query->where('maturity_rating', '<', 4)
            ->where('maturity_rating', '>=', 0);
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
