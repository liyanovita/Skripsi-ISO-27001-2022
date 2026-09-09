<?php

namespace App\Services\Assessment;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ResultService
{
    public function getResultById(int $id): AssessmentResult
    {
        $user = auth()->user();
        $query = AssessmentResult::query();
        if (!$user || !$user->isAdmin()) {
            $query->whereHas('session', function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', auth()->id()));
            });
        }
        return $query->findOrFail($id);
    }

    public function updateResult(int $id, array $data, ?UploadedFile $file = null): AssessmentResult    {
        $result = AssessmentResult::with('standard', 'session')->findOrFail($id);

        // Verify ownership — session owner OR invited user OR admin can update
        $user = auth()->user();
        $isAdmin = $user && $user->isAdmin();
        $isInvited = $result->session->invitedUsers()->where('user_id', auth()->id())->exists();
        if (!$isAdmin && $result->session->user_id !== auth()->id() && !$isInvited) {
            throw new \Exception('Unauthorized: You do not have permission to update this assessment result.');
        }

        // Lockout Guard: block updates for non-admins if session is past deadline or closed/completed
        if ($result->session->isLockedForUser($user)) {
            throw new \Exception(__('This audit session is closed/locked (past deadline or completed). Only administrators can reopen or extend it.'));
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
            $mergedAnswers = $data['answers'] ?? $result->answers ?? [];
        } else {
            $questions = $result->standard?->questions;
            $totalQuestions = is_array($questions) ? count($questions) : 0;

            $existingAnswers = is_array($result->answers) ? $result->answers : [];
            $incomingAnswers = (isset($data['answers']) && is_array($data['answers'])) ? $data['answers'] : [];
            $mergedAnswers = array_merge($existingAnswers, $incomingAnswers);

            $validAnswers = array_filter($mergedAnswers, fn($v) => $v !== null && $v !== '' && is_numeric($v));
            $answeredCount = count($validAnswers);

            $hasAnyAnswer = array_key_exists('maturity_rating', $data) || $answeredCount > 0;

            if (!$hasAnyAnswer && $result->status !== 'completed') {
                throw new \Exception('Please select a score before saving this control.');
            }

            $maturityRating = $hasAnyAnswer
                ? $this->calculateMaturityRating(array_merge($data, ['answers' => $mergedAnswers]))
                : $result->maturity_rating;

            if ($maturityRating !== null && ($maturityRating < 0 || $maturityRating > 5)) {
                throw new \Exception('Invalid maturity rating: must be between 0 and 5.');
            }

            if ($totalQuestions > 0) {
                $status = ($answeredCount >= $totalQuestions) ? 'completed' : 'in_progress';
            } else {
                $status = $hasAnyAnswer ? 'completed' : $result->status;
            }
        }

        $evidencePath = $this->handleEvidenceUpload($result, $file);

        $updateData = [
            'answers' => $mergedAnswers,
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

        $newHash = $this->computeDataHash(
            $updateData['maturity_rating'],
            (bool) $updateData['is_applicable'],
            $updateData['notes'] ?? null,
            $updateData['answers'] ?? []
        );

        if (isset($data['trigger_ai']) && $data['trigger_ai'] == '1') {
            if (Cache::get("result_{$id}_ai_status") === 'processing') {
                throw new \Exception('PROCESSING');
            }

            // Guard: block regenerate if AI recommendation exists and data has not changed since last AI generation
            $hasAiDataForHash = !empty($result->ai_recommendation) && $result->ai_data_hash === $newHash;
            if ($hasAiDataForHash) {
                throw new \Exception('NO_DATA_CHANGE');
            }
        }

        $result->update($updateData);

        if (isset($data['trigger_ai']) && $data['trigger_ai'] == '1') {
            Cache::put("result_{$id}_ai_status", 'processing', 300);
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

        // Guard: block regenerate if currently processing
        if (Cache::get("result_{$id}_ai_status") === 'processing') {
            throw new \Exception('PROCESSING');
        }

        // Guard: block regenerate if assessment data has not changed since last AI generation
        $currentHash = $this->computeResultHash($result);
        $hasAiDataForHash = !empty($result->ai_recommendation) && $result->ai_data_hash === $currentHash;

        if ($hasAiDataForHash) {
            throw new \Exception('NO_DATA_CHANGE');
        }

        // Set status to processing
        Cache::put("result_{$id}_ai_status", 'processing', 300);

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
        return $this->computeDataHash(
            $result->maturity_rating,
            (bool) $result->is_applicable,
            $result->notes,
            $result->answers
        );
    }

    /**
     * Helper to compute normalized hash of assessment input data
     */
    public function computeDataHash(?float $maturityRating, bool $isApplicable, ?string $notes, mixed $answers): string
    {
        $answersArr = is_array($answers) ? array_values($answers) : [];
        $payload = implode('|', [
            (string) ($maturityRating !== null ? (string)$maturityRating : ''),
            $isApplicable ? '1' : '0',
            trim((string) ($notes ?? '')),
            json_encode($answersArr),
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

        // New format: n8n generates both language versions in a single AI call
        // (e.g. ai_recommendation_en + ai_recommendation_id) instead of one locale
        // per call. Detect and handle it separately — no lazy translate needed after.
        if (array_key_exists('ai_recommendation_en', $data) || array_key_exists('ai_recommendation_id', $data)) {
            return $this->receiveBilingualN8nWebhook($resultId, $data);
        }

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

        // 3. Impact Interpretation — accept all common n8n key variations
        $impactInterpretation = $data['impact_interpretation'] ?? $data['impact'] ?? $data['impact_analysis'] ?? $data['interpretation'] ?? $data['impact_insight'] ?? $data['consequence'] ?? null;
        Log::info("n8n field mapping — impact_interpretation", [
            'result_id'              => $resultId,
            'impact_interpretation'  => $data['impact_interpretation'] ?? 'NOT_FOUND',
            'impact'                 => $data['impact'] ?? 'NOT_FOUND',
            'impact_analysis'        => $data['impact_analysis'] ?? 'NOT_FOUND',
            'interpretation'         => $data['interpretation'] ?? 'NOT_FOUND',
            'resolved_to'            => $impactInterpretation,
        ]);
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
                $updateData['risk_priority'] = $this->normalizeRiskPriority($targetPriority['level'] ?? null);
                if (!empty($targetPriority['justification'])) {
                    $updateData['control_insight'] = ['gap' => $targetPriority['justification']];
                }
            } else {
                $updateData['risk_priority'] = $this->normalizeRiskPriority($targetPriority);
            }
        }

        // 5. Control Insight (gap analysis) — always maps to control_insight column
        $controlInsight = $data['control_insight'] ?? $data['insight'] ?? $data['gap'] ?? $data['gap_analysis'] ?? null;
        if ($controlInsight !== null) {
            // If control_insight from prioritization_level already set, don't overwrite
            if (!isset($updateData['control_insight'])) {
                $updateData['control_insight'] = is_array($controlInsight)
                    ? $controlInsight
                    : ['gap' => $controlInsight];
            }
        }

        // 6. Evidence Validation — always maps to evidence_validation column (Disabled as AI no longer handles this)
        $updateData['evidence_validation'] = null;

        // 7. Track which locale this primary content was generated in, and invalidate
        // any previously cached translations — they described the old content.
        $requestedLocale = $data['locale'] ?? null;
        $updateData['ai_locale'] = in_array($requestedLocale, ['en', 'id'], true) ? $requestedLocale : config('app.locale');
        $updateData['ai_translations'] = null;

        Log::info("n8n webhook — updateData to be saved", array_merge(
            ['result_id' => $resultId],
            array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $updateData)
        ));

        $result->update($updateData);

        Cache::forget("result_{$resultId}_ai_status");

        return true;
    }

    /**
     * Handle the bilingual n8n payload: both "en" and "id" versions of every
     * field arrive in one webhook call, so no separate lazy-translate round trip
     * is needed for the common case. Whichever language actually came back non-empty
     * becomes the primary (canonical) content; the other is stored straight into the
     * translations sidecar, available immediately.
     */
    protected function receiveBilingualN8nWebhook($resultId, array $data): bool
    {
        $recEn = trim((string) ($data['ai_recommendation_en'] ?? ''));
        $recId = trim((string) ($data['ai_recommendation_id'] ?? ''));

        if (!$resultId || ($recEn === '' && $recId === '')) {
            $receivedKeys = implode(', ', array_keys($data));
            throw new \Exception("Missing result_id or recommendation in payload. Received keys: [{$receivedKeys}]");
        }

        $result = AssessmentResult::find($resultId);
        if (!$result) {
            throw new \Exception('AssessmentResult not found');
        }

        $field = fn(string $base, string $locale) => $data["{$base}_{$locale}"] ?? null;

        $buildContent = function (string $locale) use ($field) {
            $rec = trim((string) ($field('ai_recommendation', $locale) ?? ''));
            if ($rec === '') return null;

            return [
                'ai_recommendation'      => $rec,
                'corrective_action_plan' => !empty($field('action_plan', $locale)) ? ['action' => $field('action_plan', $locale)] : null,
                'control_insight'        => !empty($field('control_insight', $locale)) ? ['gap' => $field('control_insight', $locale)] : null,
                'impact_interpretation'  => $field('impact_interpretation', $locale),
            ];
        };

        $primaryLocale = $recEn !== '' ? 'en' : 'id';
        $secondaryLocale = $primaryLocale === 'en' ? 'id' : 'en';

        $primaryContent = $buildContent($primaryLocale);
        $secondaryContent = $buildContent($secondaryLocale);

        $updateData = array_merge($primaryContent, [
            'risk_priority'       => $this->normalizeRiskPriority($data['prioritization_level'] ?? null),
            'evidence_validation' => null,
            'ai_locale'           => $primaryLocale,
            'ai_translations'     => $secondaryContent ? [
                $secondaryLocale => $secondaryContent + ['translated_at' => now()->toDateTimeString()],
            ] : null,
        ]);

        Log::info("n8n webhook (bilingual) — updateData to be saved", ['result_id' => $resultId, 'primary_locale' => $primaryLocale]);

        $result->update($updateData);
        Cache::forget("result_{$resultId}_ai_status");

        return true;
    }

    /**
     * Normalize an AI-returned risk priority into the fixed English enum
     * ("High"/"Medium"/"Low") this codebase relies on for filtering, exports,
     * and badge coloring. The AI may respond in the assessment's active locale
     * (e.g. "Tinggi"), which must never be stored verbatim — callers elsewhere
     * match this column against literal English strings.
     */
    protected function normalizeRiskPriority(?string $raw): ?string
    {
        if ($raw === null) return null;

        $normalized = strtolower(trim($raw));
        $map = [
            'high' => 'High', 'tinggi' => 'High',
            'medium' => 'Medium', 'sedang' => 'Medium', 'menengah' => 'Medium',
            'low' => 'Low', 'rendah' => 'Low',
        ];

        return $map[$normalized] ?? null;
    }

    public function triggerEvidenceExtraction(int $resultId, string $filePath): void
    {
        $result = AssessmentResult::with(['session', 'standard'])->findOrFail($resultId);

        $user = auth()->user();
        $isAdmin = $user && $user->isAdmin();
        $isInvited = $result->session->invitedUsers()->where('user_id', auth()->id())->exists();
        if (!$isAdmin && $result->session->user_id !== auth()->id() && !$isInvited) {
            throw new \Exception('Unauthorized: You do not have permission to extract evidence for this assessment.');
        }

        if ($result->session->isLockedForUser($user)) {
            throw new \Exception(__('This audit session is closed/locked (past deadline or completed). Only administrators can reopen or extend it.'));
        }

        $files = is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]);
        if (!in_array($filePath, $files)) {
            throw new \Exception('Evidence file not found for this control.');
        }

        // Scoped per file (not just per result) so extracting one evidence file doesn't
        // block extracting another file attached to the same control at the same time.
        $lockKey = "evidence_{$resultId}_" . md5($filePath) . "_extraction_status";

        if (Cache::get($lockKey) === 'processing') {
            throw new \Exception('PROCESSING');
        }

        $webhookUrl = config('services.n8n.webhook_extraction_url');
        if (!$webhookUrl) {
            throw new \Exception('Evidence extraction is not configured.');
        }

        if (!Storage::disk('public')->exists($filePath)) {
            throw new \Exception('Evidence file not found on disk.');
        }

        Cache::put($lockKey, 'processing', 600);

        try {
            $fileContents = Storage::disk('public')->get($filePath);

            $requirementText = trim(
                ($result->standard->description ?? '')
                . ' ' . implode(' ', is_array($result->standard->questions) ? $result->standard->questions : [])
            );

            $response = Http::timeout(60)
                ->attach('file', $fileContents, basename($filePath))
                ->post($webhookUrl, [
                    'result_id'           => $resultId,
                    'file_path'           => $filePath,
                    'locale'              => app()->getLocale(),
                    'control_code'        => $result->standard->code,
                    'control_title'       => $result->standard->title,
                    'control_requirement' => $requirementText,
                ]);

            if ($response->failed()) {
                Log::error("n8n evidence extraction webhook failed for Result ID: {$resultId}");
                Cache::forget($lockKey);
                throw new \Exception('Failed to reach the evidence extraction service.');
            }
        } catch (\Exception $e) {
            Cache::forget($lockKey);
            Log::error("n8n evidence extraction connection error: " . $e->getMessage());
            throw $e;
        }
    }

    public function receiveEvidenceExtractionWebhook(array $data): bool
    {
        Log::info("Incoming evidence extraction webhook from n8n: ", $data);

        if (isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        $resultId = $data['result_id'] ?? null;
        $filePath = $data['file_path'] ?? null;

        if (!$resultId || !$filePath) {
            throw new \Exception('Missing result_id or file_path in payload.');
        }

        $result = AssessmentResult::find($resultId);
        if (!$result) {
            throw new \Exception('AssessmentResult not found');
        }

        $status = $data['status'] ?? 'failed';

        // n8n now generates both language versions of the summary in a single AI
        // call (content_summary + content_summary_id). Whichever came back non-empty
        // becomes primary; the other is stored straight into the translations sidecar —
        // no separate lazy-translate round trip needed for the common case.
        $summaryEn = trim((string) ($data['content_summary'] ?? ''));
        $summaryId = trim((string) ($data['content_summary_id'] ?? ''));
        $isLanguageAgnostic = false;
        if ($status === 'ok' && $summaryEn === '' && $summaryId === '') {
            // Short text that skipped summarization, or an image description — this is
            // raw/OCR'd document content, not an AI-composed summary in a specific
            // language, so it has no translated counterpart and must never be gated
            // behind a locale match (there's no translate workflow to produce one).
            $summaryEn = trim((string) ($data['text'] ?? ''));
            $isLanguageAgnostic = true;
        }

        // Relevance-to-control note, generated in the same bilingual AI call as the
        // summary — reuses the same primary/secondary locale decision as the summary
        // above so the two stay paired per language rather than being decided separately.
        $relevanceEn = trim((string) ($data['content_relevance'] ?? ''));
        $relevanceId = trim((string) ($data['content_relevance_id'] ?? ''));

        $requestedLocale = $data['locale'] ?? null;
        $fallbackLocale = in_array($requestedLocale, ['en', 'id'], true) ? $requestedLocale : config('app.locale');
        $primaryLocale = $isLanguageAgnostic ? $fallbackLocale : ($summaryEn !== '' ? 'en' : ($summaryId !== '' ? 'id' : $fallbackLocale));
        $primarySummary = $isLanguageAgnostic ? $summaryEn : ($primaryLocale === 'en' ? $summaryEn : $summaryId);
        $secondaryLocale = $primaryLocale === 'en' ? 'id' : 'en';
        $secondarySummary = $isLanguageAgnostic ? '' : ($primaryLocale === 'en' ? $summaryId : $summaryEn);
        $primaryRelevance = $isLanguageAgnostic ? '' : ($primaryLocale === 'en' ? $relevanceEn : $relevanceId);
        $secondaryRelevance = $isLanguageAgnostic ? '' : ($primaryLocale === 'en' ? $relevanceId : $relevanceEn);

        $extractions = is_array($result->evidence_extractions) ? $result->evidence_extractions : [];
        $extractions[$filePath] = [
            'status'             => $status,
            'summary'            => $status === 'ok' ? $primarySummary : null,
            'relevance'          => $status === 'ok' && $primaryRelevance !== '' ? $primaryRelevance : null,
            'error_reason'       => $status !== 'ok' ? ($data['error_reason'] ?? 'unknown_error') : null,
            'extracted_at'       => now()->toDateTimeString(),
            'locale'             => $primaryLocale,
            'language_agnostic'  => $isLanguageAgnostic,
            'translations'       => ($status === 'ok' && $secondarySummary !== '') ? [
                $secondaryLocale => [
                    'summary'       => $secondarySummary,
                    'relevance'     => $secondaryRelevance !== '' ? $secondaryRelevance : null,
                    'translated_at' => now()->toDateTimeString(),
                ],
            ] : null,
        ];

        $result->update(['evidence_extractions' => $extractions]);

        Cache::forget("evidence_{$resultId}_" . md5($filePath) . "_extraction_status");

        return true;
    }

    /**
     * Resolve the extraction summary for a given file/locale, mirroring
     * AssessmentResult::getAiContentForLocale()'s fallback behavior.
     */
    public function getExtractionForLocale(AssessmentResult $result, string $filePath, string $locale): ?array
    {
        $extractions = is_array($result->evidence_extractions) ? $result->evidence_extractions : [];
        $entry = $extractions[$filePath] ?? null;
        if (!$entry) return null;

        // Raw/OCR'd text with no AI-composed summary has no translated counterpart —
        // always show it as-is rather than gating it behind a locale match.
        if (!empty($entry['language_agnostic'])) {
            return $entry + ['available' => true];
        }

        $primaryLocale = $entry['locale'] ?? config('app.locale');
        if ($locale === $primaryLocale || ($entry['status'] ?? null) !== 'ok') {
            return $entry + ['available' => true];
        }

        $translations = is_array($entry['translations'] ?? null) ? $entry['translations'] : [];
        if (isset($translations[$locale]['summary'])) {
            return array_merge($entry, [
                'summary'   => $translations[$locale]['summary'],
                'relevance' => $translations[$locale]['relevance'] ?? null,
                'available' => true,
            ]);
        }

        return $entry + ['available' => false];
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
                Cache::forget("result_{$result->id}_ai_status");
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
                    'code'        => $result->standard->code,
                    'title'       => $result->standard->title,
                    'description' => $result->standard->description,
                    'guidance'    => $result->standard->implementation_guidance,
                ],
                'assessment' => [
                    'maturity_rating'    => $result->maturity_rating,
                    'answers'            => $result->answers,
                    'notes'              => $result->notes,
                    'evidence'           => !empty($result->evidence_file)
                        ? implode(', ', array_map(fn($f) => basename($f), (array) $result->evidence_file))
                        : null,
                    'risk_level'         => $result->risk_level,
                    'compliance_status'  => $result->compliance_status,
                ],
                'locale'    => app()->getLocale(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            if ($response->failed()) {
                Log::error("n8n Webhook failed for Result ID: {$result->id}");
                Cache::forget("result_{$result->id}_ai_status");
            }
            
        } catch (\Exception $e) {
            Log::error("n8n Connection Error: " . $e->getMessage());
            Cache::forget("result_{$result->id}_ai_status");
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
            $insight = "Terdapat kesenjangan antara praktik aktual dan persyaratan dokumentasi formal ISO 27001.";
        } else {
            $recommendation = "Based on the maturity rating of {$result->maturity_rating} for control {$result->standard->code}, it is recommended to establish formal policy documentation covering standard operating procedures (SOPs). This policy should be regularly disseminated to all relevant staff.";
            $actionPlan = "1. Draft policies and SOPs related to {$result->standard->title}.\n2. Obtain approval from senior management.\n3. Conduct awareness training for all key personnel.";
            $impact = "Without implementing this control, the organization faces risks of operational inconsistency and potential non-compliance during external audits.";
            $priority = "High";
            $insight = "A gap exists between actual practices and the formal documentation requirements of ISO 27001.";
        }

        $result->update([
            'ai_recommendation' => $recommendation,
            'corrective_action_plan' => ['action' => $actionPlan],
            'impact_interpretation' => $impact,
            'risk_priority' => $priority,
            'evidence_validation' => null,
            'control_insight' => ['gap' => $insight]
        ]);
    }

    protected function updateSessionScore(int $sessionId): void
    {
        $session = AssessmentSession::findOrFail($sessionId);
        $session->calculateMaturityScore();
        if ($session->status !== 'completed') {
            $session->update(['status' => 'in_progress']);
        }
    }
}
