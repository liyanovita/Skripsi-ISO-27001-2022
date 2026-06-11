<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'standard_id' => $this->standard_id,
            'maturity_rating' => $this->maturity_rating,
            'compliance_status' => $this->compliance_status,
            'risk_level' => $this->risk_level,
            'notes' => $this->notes,
            'evidence_file' => $this->evidence_file,
            'ai_recommendation' => $this->ai_recommendation,
            'corrective_action_plan' => $this->corrective_action_plan,
            'control_insight' => $this->control_insight,
            'risk_priority' => $this->risk_priority,
            'evidence_validation' => $this->evidence_validation,
            
            // SoA fields
            'is_applicable' => $this->is_applicable,
            'soa_justification' => $this->soa_justification,
            'treatment_due_date' => $this->treatment_due_date,
            'treatment_pic' => $this->treatment_pic,
            'treatment_status' => $this->treatment_status,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include standard when loaded
            'standard' => $this->whenLoaded('standard', function () {
                return [
                    'id' => $this->standard->id,
                    'code' => $this->standard->code,
                    'title' => $this->standard->title,
                    'type' => $this->standard->type,
                ];
            }),
        ];
    }
}