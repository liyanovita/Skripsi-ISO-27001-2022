<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentSessionResource extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->name,
            'status' => $this->status,
            'overall_maturity_score' => $this->overall_maturity_score,
            'ai_summary' => $this->ai_summary,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            
            // Include results when loaded
            'results' => AssessmentResultResource::collection($this->whenLoaded('results')),
            
            // Include statistics when available
            'statistics' => $this->when(isset($this->statistics), $this->statistics),
        ];
    }
}