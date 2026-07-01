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

        $newPayload = implode('|', [
            (string) $updateData['maturity_rating'],
            $updateData['is_applicable'] ? '1' : '0',
            (string) ($updateData['notes'] ?? ''),
            json_encode($updateData['answers'] ?? []),
        ]);
        $newHash = hash('sha256', $newPayload);

        if (isset($data['trigger_ai']) && $data['trigger_ai'] == '1') {
            if ($result->ai_recommendation && $result->ai_data_hash === $newHash) {
                throw new \Exception('NO_DATA_CHANGE');
            }
        }

        $result->update($updateData);

        if (isset($data['trigger_ai']) && $data['trigger_ai'] == '1') {
            $result->update([
                'ai_recommendation' => null,
                'corrective_action_plan' => null,
                'control_insight' => null,
                'risk_priority' => null,
                'evidence_validation' => null,
                'ai_data_hash' => $newHash,
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

        // Guard: block regenerate if assessment data has not changed since last AI generation
        $currentHash = $this->computeResultHash($result);
        if ($result->ai_data_hash && $result->ai_recommendation && $result->ai_data_hash === $currentHash) {
            throw new \Exception('NO_DATA_CHANGE');
        }

        $result->update([
            'ai_recommendation'      => null,
            'corrective_action_plan' => null,
            'control_insight'        => null,
            'risk_priority'          => null,
            'evidence_validation'    => null,
            'ai_data_hash'           => $currentHash, // snapshot data at generation time
        ]);

        $this->sendToN8n($result);

        return true;
    }

    /**
     * Compute a SHA-256 hash of the assessment data fields that are sent to the AI.
     * Only changes to these fields should allow a regeneration.
     */
    public function computeResultHash(AssessmentResult $result): string
    {
        $payload = implode('|', [
            (string) $result->maturity_rating,
            $result->is_applicable ? '1' : '0',
            (string) ($result->notes ?? ''),
            json_encode($result->answers ?? []),
        ]);

        return hash('sha256', $payload);
    }

    public function receiveN8nWebhook(array $data): bool
    {
        Log::info("Incoming Webhook from n8n Payload: ", $data);

        // Auto-unwrap jika n8n mengirim data di dalam array [ { ... } ]
        if (isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        $resultId = $data['result_id'] ?? $data['id'] ?? null;
        
        $strategicRecommendation = $data['strategic_recommendation'] ?? null;
        $aiRecommendation = $data['ai_recommendation'] ?? null;
        $recommendation = $data['recommendation'] ?? null;
        $targetRecommendation = $strategicRecommendation ?? $aiRecommendation ?? $recommendation;

        if (!$resultId || !$targetRecommendation) {
            $receivedKeys = implode(', ', array_keys($data));
            throw new \Exception("Missing result_id or recommendation in payload. Received keys: [{$receivedKeys}]");
        }

        $result = AssessmentResult::find($resultId);
        if (!$result) {
            throw new \Exception('AssessmentResult not found');
        }

        $updateData = [];

        // 1. Recommendation
        $updateData['ai_recommendation'] = $targetRecommendation;

        // 2. Action Plan / Corrective Action Plan
        $actionPlan = $data['action_plan'] ?? $data['corrective_action_plan'] ?? $data['corrective_action'] ?? $data['action'] ?? null;
        if ($actionPlan !== null) {
            $updateData['corrective_action_plan'] = is_array($actionPlan) ? $actionPlan : ['action' => $actionPlan];
        }

        // 3. Impact Interpretation
        $impactInterpretation = $data['impact_interpretation'] ?? $data['impact'] ?? $data['impact_analysis'] ?? $data['interpretation'] ?? null;
        if ($impactInterpretation !== null) {
            $updateData['impact_interpretation'] = $impactInterpretation;
        }

        // 4. Prioritization Level / Risk Priority
        $prioritizationLevel = $data['prioritization_level'] ?? null;
        $riskPriority = $data['risk_priority'] ?? null;
        $priority = $data['priority'] ?? null;
        $prioritization = $data['prioritization'] ?? null;
        $targetPriority = $prioritizationLevel ?? $riskPriority ?? $priority ?? $prioritization;

        if ($targetPriority !== null) {
            if (is_array($targetPriority)) {
                $updateData['risk_priority'] = $targetPriority['level'] ?? null;
                if (!empty($targetPriority['justification'])) {
                    $updateData['control_insight'] = ['gap' => $targetPriority['justification']];
                }
            } else {
                $updateData['risk_priority'] = $targetPriority;
            }
        }

        // 5. Control Insight / Evidence Validation
        $hasNewKeys = isset($data['strategic_recommendation']) || 
                      isset($data['prioritization_level']) || 
                      isset($data['impact_interpretation']) || 
                      isset($data['evidence_validation']) ||
                      isset($data['impact']) ||
                      isset($data['priority']);

        $controlInsight = $data['control_insight'] ?? $data['insight'] ?? $data['gap'] ?? null;
        if ($controlInsight !== null) {
            if ($hasNewKeys) {
                $updateData['evidence_validation'] = $controlInsight;
            } else {
                $currentInsight = $result->control_insight ?? [];
                if (is_array($currentInsight)) {
                    $currentInsight['gap'] = $controlInsight;
                } else {
                    $currentInsight = ['gap' => $controlInsight];
                }
                $updateData['control_insight'] = $currentInsight;
            }
        }

        $evidenceValidation = $data['evidence_validation'] ?? $data['validation'] ?? null;
        if ($evidenceValidation !== null) {
            $updateData['evidence_validation'] = $evidenceValidation;
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
                Log::warning("N8N_WEBHOOK_URL not configured, fallback to generating mock AI insight for Result ID: {$result->id}");
                $this->generateMockAiInsight($result);
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
                'locale'        => app()->getLocale(),
                'timestamp'     => now()->toDateTimeString(),
            ]);

            if ($response->failed()) {
                Log::error("n8n Webhook failed for Result ID: {$result->id}");
            }
            
        } catch (\Exception $e) {
            Log::error("n8n Connection Error: " . $e->getMessage());
        }
    }

    protected function generateMockAiInsight(AssessmentResult $result): void
    {
        $isId = app()->getLocale() === 'id';
        
        if ($isId) {
            $recommendation = "Berdasarkan penilaian tingkat kematangan {$result->maturity_rating} untuk kontrol {$result->standard->code}, disarankan untuk menetapkan dokumentasi kebijakan formal yang mencakup prosedur operasional standar (SOP). Kebijakan ini harus disosialisasikan secara berkala kepada seluruh staf terkait.";
            $actionPlan = "1. Menyusun draf kebijakan dan SOP terkait {$result->standard->title}.\n2. Memperoleh persetujuan dari pimpinan organisasi.\n3. Melaksanakan pelatihan kesadaran untuk seluruh personel.";
            $impact = "Tanpa penerapan kontrol ini, organisasi berisiko mengalami inkonsistensi operasional dan potensi ketidakpatuhan terhadap persyaratan audit eksternal.";
            $priority = "Tinggi";
            $validation = "Catatan bukti yang ada saat ini (" . ($result->notes ?: 'Tidak ada') . ") menunjukkan perlunya peningkatan formalitas dalam dokumentasi.";
            $insight = "Terdapat kesenjangan antara praktik aktual dan persyaratan dokumentasi formal ISO 27001.";
        } else {
            $recommendation = "Based on the maturity rating of {$result->maturity_rating} for control {$result->standard->code}, it is recommended to establish formal policy documentation covering standard operating procedures (SOPs). This policy should be regularly disseminated to all relevant staff.";
            $actionPlan = "1. Draft policies and SOPs related to {$result->standard->title}.\n2. Obtain approval from senior management.\n3. Conduct awareness training for all key personnel.";
            $impact = "Without implementing this control, the organization faces risks of operational inconsistency and potential non-compliance during external audits.";
            $priority = "High";
            $validation = "The current evidence notes (" . ($result->notes ?: 'None') . ") indicate a need for increased formality in documentation.";
            $insight = "A gap exists between actual practices and the formal documentation requirements of ISO 27001.";
        }

        $result->update([
            'ai_recommendation' => $recommendation,
            'corrective_action_plan' => ['action' => $actionPlan],
            'impact_interpretation' => $impact,
            'risk_priority' => $priority,
            'evidence_validation' => $validation,
            'control_insight' => ['gap' => $insight]
        ]);
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
