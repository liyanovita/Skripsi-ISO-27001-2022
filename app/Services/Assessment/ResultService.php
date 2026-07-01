<?php

namespace App\Services\Assessment;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResultService
{
    public function getResultById(int $id): AssessmentResult
    {
        return AssessmentResult::whereHas('session', function($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);
    }

    public function updateResult(int $id, array $data, ?UploadedFile $file = null): AssessmentResult    {
        $result = AssessmentResult::with('standard', 'session')->findOrFail($id);

        // Verify ownership - ensure result belongs to authenticated user
        if ($result->session->user_id !== auth()->id()) {
            throw new \Exception('Unauthorized: You do not have permission to update this assessment result.');
        }

        $isClause = in_array($result->standard->type ?? '', ['clause', 'clausa']);

        // Determine applicability
        if ($isClause) {
            $isApplicable = true;
        } else {
            $isApplicable = array_key_exists('is_applicable', $data)
                ? filter_var($data['is_applicable'], FILTER_VALIDATE_BOOLEAN)
                : $result->is_applicable;
        }

        // Non-applicable controls don't require scores — skip score logic entirely
        if (!$isApplicable) {
            $maturityRating = null;
            $status = 'completed';
        } else {
            // Score is the only required assessment input. Evidence, notes, PIC,
            // and due date remain optional.
            $hasSubmittedScore = array_key_exists('maturity_rating', $data)
                || (isset($data['answers']) && is_array($data['answers']) && count($data['answers']) > 0);

            if (!$hasSubmittedScore && $result->status !== 'completed') {
                throw new \Exception('Please select a score before saving this control.');
            }

            $maturityRating = $hasSubmittedScore
                ? $this->calculateMaturityRating($data)
                : $result->maturity_rating;
            if ($maturityRating !== null && ($maturityRating < 0 || $maturityRating > 5)) {
                throw new \Exception('Invalid maturity rating: must be between 0 and 5.');
            }
            $status = $hasSubmittedScore ? 'completed' : $result->status;
        }

        $evidencePath = $this->handleEvidenceUpload($result, $file);

        $updateData = [
            'answers' => $data['answers'] ?? [],
            'maturity_rating' => $maturityRating,
            'notes' => $data['notes'] ?? null,
            'evidence_file' => $evidencePath,
            'status' => $status,
            'treatment_pic' => $data['treatment_pic'] ?? null,
            'treatment_due_date' => $data['treatment_due_date'] ?? null,
            'is_applicable' => $isApplicable,
        ];

        if (array_key_exists('soa_justification', $data)) {
            $updateData['soa_justification'] = $data['soa_justification'];
        }

        $result->update($updateData);

        if (isset($data['trigger_ai']) && $data['trigger_ai'] == '1') {
            $result->update([
                'ai_recommendation' => null,
                'corrective_action_plan' => null,
                'control_insight' => null,
                'risk_priority' => null,
                'evidence_validation' => null,
            ]);
            $this->sendToN8n($result);
        }

        $this->updateSessionScore($result->session_id);

        return $result;
    }

    public function generateAiInsight(int $id): bool
    {
        $result = AssessmentResult::with('standard', 'session')->findOrFail($id);

        // Verify ownership
        if ($result->session->user_id !== auth()->id()) {
            throw new \Exception('Unauthorized: You do not have permission to generate insights for this assessment.');
        }

        $result->update([
            'ai_recommendation' => null,
            'corrective_action_plan' => null,
            'control_insight' => null,
            'risk_priority' => null,
            'evidence_validation' => null,
        ]);

        $this->sendToN8n($result);

        return true;
    }

    public function receiveN8nWebhook(array $data): bool
    {
        Log::info("Incoming Webhook from n8n Payload: ", $data);

        // Auto-unwrap jika n8n mengirim data di dalam array [ { ... } ]
        if (isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        $resultId = $data['result_id'] ?? null;
        $strategicRecommendation = $data['strategic_recommendation'] ?? null;
        $aiRecommendation = $data['ai_recommendation'] ?? null;

        $targetRecommendation = $strategicRecommendation ?? $aiRecommendation;

        if (!$resultId || !$targetRecommendation) {
            $receivedKeys = implode(', ', array_keys($data));
            throw new \Exception("Missing result_id or recommendation in payload. Received keys: [{$receivedKeys}]");
        }

        $result = AssessmentResult::find($resultId);
        if (!$result) {
            throw new \Exception('AssessmentResult not found');
        }

        $updateData = [];

        // Check if new payload structure is detected (by presence of strategic_recommendation)
        if ($strategicRecommendation !== null) {
            $updateData['ai_recommendation'] = $strategicRecommendation;

            // Prioritization level handling (object)
            $prioritizationLevel = $data['prioritization_level'] ?? null;
            $priority = null;
            $justification = null;
            if (is_array($prioritizationLevel)) {
                $priority = $prioritizationLevel['level'] ?? null;
                $justification = $prioritizationLevel['justification'] ?? null;
            } else {
                $priority = $prioritizationLevel;
            }

            if ($priority !== null) {
                $updateData['risk_priority'] = $priority;
            }

            if ($justification !== null) {
                $updateData['control_insight'] = ['gap' => $justification];
            }

            // Action plan (wrap in corrective_action_plan schema: ['action' => $actionPlan])
            $actionPlan = $data['action_plan'] ?? null;
            if ($actionPlan !== null) {
                $updateData['corrective_action_plan'] = is_array($actionPlan) ? $actionPlan : ['action' => $actionPlan];
            }

            // Control insight (corresponds to evidence validation in DB/UI)
            $controlInsight = $data['control_insight'] ?? null;
            if ($controlInsight !== null) {
                $updateData['evidence_validation'] = $controlInsight;
            }

            // Impact interpretation
            $impactInterpretation = $data['impact_interpretation'] ?? null;
            if ($impactInterpretation !== null) {
                $updateData['impact_interpretation'] = $impactInterpretation;
            }
        } else {
            // Fallback to old payload structure keys
            $updateData['ai_recommendation'] = $aiRecommendation;

            if (isset($data['action_plan'])) {
                $actionPlan = $data['action_plan'];
                $updateData['corrective_action_plan'] = is_array($actionPlan) ? $actionPlan : ['action' => $actionPlan];
            }
            if (isset($data['control_insight'])) {
                $currentInsight = $result->control_insight ?? [];
                if (is_array($currentInsight)) {
                    $currentInsight['gap'] = $data['control_insight'];
                } else {
                    $currentInsight = ['gap' => $data['control_insight']];
                }
                $updateData['control_insight'] = $currentInsight;
            }
            if (isset($data['evidence_validation'])) {
                $updateData['evidence_validation'] = $data['evidence_validation'];
            }
            if (isset($data['impact_interpretation'])) {
                $updateData['impact_interpretation'] = $data['impact_interpretation'];
            }
            if (isset($data['risk_priority'])) {
                $updateData['risk_priority'] = $data['risk_priority'];
            }
        }

        $result->update($updateData);

        return true;
    }

    protected function calculateMaturityRating(array $data): int
    {
        if (isset($data['maturity_rating'])) {
            $rating = (int) $data['maturity_rating'];
            // Validate rating is within acceptable range
            if ($rating < 0 || $rating > 5) {
                throw new \Exception('Invalid maturity rating: must be between 0 and 5.');
            }
            return $rating;
        }

        if (isset($data['answers']) && is_array($data['answers'])) {
            $scores = array_filter($data['answers'], fn($v) => is_numeric($v));
            if (count($scores) > 0) {
                $avg = round(array_sum($scores) / count($scores));
                // Ensure calculated rating is within range
                return max(0, min(5, (int) $avg));
            }
        }

        return 0;
    }

    protected function handleEvidenceUpload(AssessmentResult $result, ?UploadedFile $file): ?array
    {
        $currentFiles = is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]);

        if (!$file) {
            return $currentFiles;
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        
        $cleanName = \Illuminate\Support\Str::slug($originalName);
        $codeSlug = \Illuminate\Support\Str::slug($result->standard->code);
        $fileName = 'evidence-' . $codeSlug . '-' . $cleanName . '-' . time() . '.' . $extension;

        $path = $file->storeAs('evidence/' . $result->session_id, $fileName, 'public');
        
        $currentFiles[] = $path;

        return $currentFiles;
    }

    protected function sendToN8n(AssessmentResult $result): void
    {
        try {
            $webhookUrl = config('services.n8n.webhook_url');

            if (!$webhookUrl) {
                Log::warning("N8N_WEBHOOK_URL not configured, skipping AI insight for Result ID: {$result->id}");
                return;
            }
            
            $response = Http::timeout(60)->post($webhookUrl, [
                'result_id'     => $result->id,
                'session_name'  => $result->session->name ?? 'Internal Audit',
                'organization'  => [
                    'scale' => auth()->user()->organization_scale ?? 'N/A',
                    'scope' => auth()->user()->isms_scope ?? 'N/A',
                ],
                'control' => [
                    'code'  => $result->standard->code,
                    'title' => $result->standard->title,
                    'description' => $result->standard->description,
                    'guidance' => $result->standard->implementation_guidance,
                ],
                'assessment' => [
                    'maturity_rating' => $result->maturity_rating,
                    'answers' => $result->answers,
                    'notes' => $result->notes,
                    'risk_level' => $result->risk_level,
                    'compliance_status' => $result->compliance_status,
                ],
                'timestamp'     => now()->toDateTimeString(),
            ]);

            if ($response->failed()) {
                Log::error("n8n Webhook failed for Result ID: {$result->id}");
            }
            
        } catch (\Exception $e) {
            Log::error("n8n Connection Error: " . $e->getMessage());
        }
    }

    protected function updateSessionScore(int $sessionId): void
    {
        $session = AssessmentSession::findOrFail($sessionId);
        
        $avg = AssessmentResult::where('session_id', $sessionId)
            ->where('status', 'completed')
            ->where('is_applicable', true)
            ->whereHas('standard', function ($query) {
                $query->whereNotNull('questions');
            })
            ->avg('maturity_rating');

        $hasCompletedControls = AssessmentResult::where('session_id', $sessionId)
            ->where('status', 'completed')
            ->where('is_applicable', true)
            ->whereHas('standard', function ($query) {
                $query->whereNotNull('questions');
            })
            ->exists();
        
        $session->update([
            'overall_maturity_score' => round($avg ?? 0, 2),
            'status' => $session->status === 'completed'
                ? 'completed'
                : 'in_progress'
        ]);
    }
}
