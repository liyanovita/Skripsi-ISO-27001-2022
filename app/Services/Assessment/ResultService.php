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

    public function triggerEvidenceExtraction(int $resultId, string $filePath, bool $force = false): void
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

        if ($force) {
            Cache::forget($lockKey);
        } elseif (Cache::get($lockKey) === 'processing') {
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

        // Mark existing extraction status as processing so polling waits for new extraction
        $extractions = is_array($result->evidence_extractions) ? $result->evidence_extractions : [];
        if (isset($extractions[$filePath])) {
            $extractions[$filePath]['status'] = 'processing';
            $result->update(['evidence_extractions' => $extractions]);
        }

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
                if (isset($extractions[$filePath])) {
                    $extractions[$filePath]['status'] = 'failed';
                    $result->update(['evidence_extractions' => $extractions]);
                }
                throw new \Exception('Failed to reach the evidence extraction service.');
            }
        } catch (\Exception $e) {
            Cache::forget($lockKey);
            if (isset($extractions[$filePath])) {
                $extractions[$filePath]['status'] = 'failed';
                $result->update(['evidence_extractions' => $extractions]);
            }
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
        $relevanceStatus = strtoupper(trim((string) ($data['relevance_status'] ?? $data['content_relevance_status'] ?? '')));

        if ($relevanceStatus === '' && ($relevanceEn !== '' || $relevanceId !== '')) {
            $combined = strtolower($relevanceId . ' ' . $relevanceEn);
            $negPattern = '/\b(tidak (tampak |secara langsung |memiliki )?(relevan|berhubungan|berkaitan|mencakup|memenuhi|sesuai|kaitan)|bukan (merupakan )?bukti|kurang relevan|not (directly |clearly )?relevant|does not (appear |seem )?(to be )?relat(e|ed)|is not related|unrelated|irrelevant|not related|has no relevance|does not satisfy|does not demonstrate|does not align)\b/i';
            $relevanceStatus = preg_match($negPattern, $combined) ? 'NOT_RELEVANT' : 'RELEVANT';
        }

        $requestedLocale = $data['locale'] ?? null;
        $fallbackLocale = in_array($requestedLocale, ['en', 'id'], true) ? $requestedLocale : config('app.locale');

        // Normalize relevance text to prevent language mixing
        $controlCode = (string) ($data['control_code'] ?? ($result->standard->code ?? ''));
        if ($relevanceId !== '') {
            $relevanceId = $this->translateRelevanceToId($relevanceId, $controlCode, $relevanceStatus);
        } elseif ($relevanceEn !== '') {
            $relevanceId = $this->translateRelevanceToId($relevanceEn, $controlCode, $relevanceStatus);
        }

        if ($relevanceEn === '' && $relevanceId !== '') {
            $relevanceEn = $this->translateRelevanceToEn($relevanceId, $controlCode, $relevanceStatus);
        }

        // Align primary locale with requested locale or Indonesian default
        if ($requestedLocale === 'id' && $summaryId !== '') {
            $primaryLocale = 'id';
        } elseif ($requestedLocale === 'en' && $summaryEn !== '') {
            $primaryLocale = 'en';
        } else {
            $primaryLocale = $isLanguageAgnostic ? $fallbackLocale : ($summaryId !== '' ? 'id' : ($summaryEn !== '' ? 'en' : $fallbackLocale));
        }

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
            'relevance_status'   => $status === 'ok' && $relevanceStatus !== '' ? $relevanceStatus : null,
            'error_reason'       => $status !== 'ok' ? ($data['error_reason'] ?? 'unknown_error') : null,
            'extracted_at'       => now()->toDateTimeString(),
            'locale'             => $primaryLocale,
            'language_agnostic'  => $isLanguageAgnostic,
            'translations'       => ($status === 'ok' && ($secondarySummary !== '' || $secondaryRelevance !== '')) ? [
                $secondaryLocale => [
                    'summary'          => $secondarySummary !== '' ? $secondarySummary : $primarySummary,
                    'relevance'        => $secondaryRelevance !== '' ? $secondaryRelevance : null,
                    'relevance_status' => $relevanceStatus !== '' ? $relevanceStatus : null,
                    'translated_at'    => now()->toDateTimeString(),
                ],
            ] : null,
        ];

        $result->update(['evidence_extractions' => $extractions]);

        Cache::forget("evidence_{$resultId}_" . md5($filePath) . "_extraction_status");

        return true;
    }

    /**
     * Resolve the extraction summary for a given file/locale, ensuring
     * summary and relevance are ALWAYS in the exact same language.
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
        $translations = is_array($entry['translations'] ?? null) ? $entry['translations'] : [];
        $controlCode = $result->standard->code ?? '';

        if ($locale === 'id') {
            $summary = ($primaryLocale === 'id')
                ? ($entry['summary'] ?? null)
                : ($translations['id']['summary'] ?? $entry['summary'] ?? null);

            $relevance = ($primaryLocale === 'id')
                ? ($entry['relevance'] ?? null)
                : ($translations['id']['relevance'] ?? null);

            if (empty($relevance) && !empty($entry['relevance'])) {
                $relevance = $this->translateRelevanceToId($entry['relevance'], $controlCode, $entry['relevance_status'] ?? '');
            } elseif (!empty($relevance)) {
                $relevance = $this->translateRelevanceToId($relevance, $controlCode, $entry['relevance_status'] ?? '');
            }

            return array_merge($entry, [
                'summary'   => $summary,
                'relevance' => $relevance,
                'available' => !empty($summary),
            ]);
        }

        if ($locale === 'en') {
            $summary = ($primaryLocale === 'en')
                ? ($entry['summary'] ?? null)
                : ($translations['en']['summary'] ?? $entry['summary'] ?? null);

            $relevance = ($primaryLocale === 'en')
                ? ($entry['relevance'] ?? null)
                : ($translations['en']['relevance'] ?? null);

            if (empty($relevance) && !empty($entry['relevance'])) {
                $relevance = $this->translateRelevanceToEn($entry['relevance'], $controlCode, $entry['relevance_status'] ?? '');
            }

            return array_merge($entry, [
                'summary'   => $summary,
                'relevance' => $relevance,
                'available' => !empty($summary),
            ]);
        }

        if ($locale === $primaryLocale || ($entry['status'] ?? null) !== 'ok') {
            return $entry + ['available' => true];
        }

        if (isset($translations[$locale]['summary'])) {
            return array_merge($entry, [
                'summary'   => $translations[$locale]['summary'],
                'relevance' => $translations[$locale]['relevance'] ?? $entry['relevance'] ?? null,
                'available' => true,
            ]);
        }

        return $entry + ['available' => false];
    }

    /**
     * Clean and translate relevance explanations to formal Indonesian.
     */
    public function translateRelevanceToId(string $text, string $controlCode = '', string $status = ''): string
    {
$t = trim($text);
    if ($t === '') return '';

    // Step 1: Whole custom audit sentences
    $wholeClauses = [
        '/\bThe documents are highly relevant because they constitute the primary, operational evidence for the entire Management Review cycle \([^)]+\)\.?\s*Specifically, Document 3 \([^)]+\) serves as the legally binding minutes, demonstrating that top management \([^)]+\) formally convened, reviewed the ISMS inputs, and made strategic decisions regarding its suitability, adequacy, and effectiveness, directly satisfying the core requirement of Clause ([^\s.]+)\b/i'
            => "Dokumen-dokumen ini sangat relevan karena merupakan bukti operasional utama untuk seluruh siklus Tinjauan Manajemen. Secara khusus, Dokumen 3 berfungsi sebagai notulen resmi yang membuktikan bahwa manajemen puncak (Direksi) secara formal mengadakan rapat, meninjau masukan SMKI, dan mengambil keputusan strategis terkait kesesuaian dan efektivitasnya, yang secara langsung memenuhi persyaratan Klausul $1",

        '/\bThe documents are highly relevant because they constitute the primary, operational evidence for the entire Management Review cycle\b/i'
            => "Dokumen-dokumen ini sangat relevan karena merupakan bukti operasional utama untuk seluruh siklus Tinjauan Manajemen",

        '/\bThe document provided is an ISMS Scope Statement \(Clause 4\.3\)\.?\s*(While the document|Meskipun dokumen ini) mentions that internal and external issues \(Clause 4\.1\) were considered during its creation, the document itself is a scope definition, not the required ((primary\s+)?operational evidence|bukti operasional) \(such as a PESTLE or Issues Register\) detailing the identified internal and external issues\. (Therefore, it is not|Oleh karena itu, dokumen ini (it is not|not)) (direct evidence|bukti langsung) for Control 4\.1\.?\b/i'
            => "Dokumen yang dilampirkan merupakan Pernyataan Ruang Lingkup SMKI (Klausul 4.3). Meskipun dokumen ini menyebutkan bahwa isu internal dan eksternal (Klausul 4.1) telah dipertimbangkan, dokumen ini sendiri merupakan definisi ruang lingkup, bukan bukti operasional (seperti PESTLE atau Register Isu) yang merinci analisis isu internal dan eksternal. Oleh karena itu, bukan merupakan bukti langsung untuk Kontrol 4.1.",

        '/\bDokumen ini SANGAT RELEVAN karena it is a comprehensive, primary operational Standard Operating Procedure \(SOP\) explicitly designed to govern and satisfy the requirements of ISO\/IEC 27001:2022 Clause 7\.5\.3\b/i'
            => "Dokumen ini sangat relevan karena merupakan Standar Operasional Prosedur (SOP) operasional utama yang komprehensif yang secara khusus dirancang untuk mengatur dan memenuhi persyaratan Klausul 7.5.3 ISO/IEC 27001:2022",

        '/\bcomprehensive, primary operational Standard Operating Procedure \(SOP\) explicitly designed to govern and satisfy (the requirements of|persyaratan dari) ISO\/IEC 27001:2022 Clause 7\.5\.3\b/i'
            => "Standar Operasional Prosedur (SOP) utama yang dirancang komprehensif untuk mengatur dan memenuhi persyaratan Klausul 7.5.3 ISO/IEC 27001:2022",

        '/\bThe document provided is an ISMS Scope Statement \(Clause 4\.3\),? not a dedicated Stakeholder Register or Stakeholder Analysis Matrix\. While the document references the consideration of interested parties\' needs and expectations \(Clause 4\.2\) in its justification section, it does not contain the primary operational evidence—such as a list of identified parties, their specific requirements, or the resulting gap analysis—required to directly audit compliance with Clause 4\.2\. Therefore, (it is|merupakan) not direct evidence for this specific control\b/i'
            => "Dokumen yang dilampirkan merupakan Pernyataan Ruang Lingkup SMKI (Klausul 4.3), bukan Register Pemangku Kepentingan atau Matriks Analisis Pemangku Kepentingan tersendiri. Meskipun dokumen mengacu pada pertimbangan kebutuhan dan harapan pihak berkepentingan (Klausul 4.2), dokumen ini tidak memuat bukti operasional utama—seperti daftar pihak terkait, persyaratan spesifik, atau analisis kesenjangan—yang dipersyaratkan untuk mengaudit kepatuhan Klausul 4.2. Oleh karena itu, bukan merupakan bukti langsung untuk kontrol ini",

        '/\bThe document is RELEVANT because, although it is a high-level Policy bukan a standalone Scope Statement, it serves as the primary, approved governance artifact that explicitly defines and mandates the boundaries and applicability of the ISMS\. It details the organizational units \([^)]+\), the technological boundaries \([^)]+\), and the physical boundaries \([^)]+\) that must be included in the scope, thereby satisfying the requirement of Control 4\.3\b/i'
            => "Dokumen ini RELEVAN karena meskipun merupakan kebijakan tingkat tinggi dan bukan Pernyataan Ruang Lingkup tersendiri, dokumen berfungsi sebagai artefak tata kelola utama yang secara eksplisit menetapkan batasan dan keberlakuan SMKI, mencakup unit organisasi, batasan teknologi, dan batasan fisik, sehingga memenuhi persyaratan Kontrol 4.3",

        '/\bThe document is NOT_RELEVANT as primary evidence for Control 4\.3 \(Determining the scope of the ISMS\)\. While this SWOT & PESTLE Analysis \(ISMS-CTX-001\) is highly relevant foundational evidence for Clause 4\.1 \(Understanding the Context\), it is not the formal, definitive ISMS Scope Statement\. The scope statement must explicitly define the boundaries \(physical, organizational, and technological\) in a dedicated document, whereas this document only provides the contextual inputs that \*inform\* the scope determination\b/i'
            => "Dokumen ini TIDAK RELEVAN sebagai bukti utama untuk Kontrol 4.3 (Menentukan ruang lingkup SMKI). Meskipun Analisis SWOT & PESTLE (ISMS-CTX-001) ini merupakan bukti dasar yang relevan untuk Klausul 4.1 (Memahami Konteks), dokumen ini bukan merupakan Pernyataan Ruang Lingkup SMKI formal yang mendefinisikan batasan fisik, organisasi, dan teknologi",

        '/\bThe document is NOT_RELEVANT as primary operational evidence for Control 5\.1 \(Leadership and commitment\)\. This document is a Context Analysis \(Clause 4\.1\), which is a foundational input that informs the ISMS, bukan the direct evidence of top management\'s commitment or the ISMS Policy itself\. While the document does mention \'Dukungan manajemen puncak\' \(Top management support\) as a strength, the required evidence for 5\.1 would be the formal ISMS Policy document or the Management Review Minutes \(Clause 9\.3\) where leadership explicitly demonstrates commitment and alignment\b/i'
            => "Dokumen ini TIDAK RELEVAN sebagai bukti operasional utama untuk Kontrol 5.1 (Kepemimpinan dan komitmen). Dokumen ini merupakan Analisis Konteks (Klausul 4.1) yang menjadi masukan dasar SMKI, bukan bukti langsung komitmen manajemen puncak atau Kebijakan SMKI itu sendiri. Bukti yang dipersyaratkan untuk 5.1 adalah dokumen Kebijakan SMKI formal atau Notulen Tinjauan Manajemen (Klausul 9.3)",

        '/\bNOT_RELEVANT\. This document is an academic research paper analyzing the implementation gap of an ISMS, not a primary operational artifact\. While it discusses the \*finding\* that roles and responsibilities are inconsistently understood \(Tabel 1, Point 7\), it does not provide the required primary evidence, such as a formal RACI matrix, organizational chart, or job descriptions, that directly satisfies the requirement for Control 5\.3\b/i'
            => "TIDAK RELEVAN. Dokumen ini merupakan karya ilmiah akademik yang menganalisis kesenjangan implementasi SMKI, bukan merupakan artefak operasional utama. Dokumen tidak menyediakan bukti utama yang dipersyaratkan seperti matriks RACI formal, struktur organisasi, atau uraian pekerjaan yang secara langsung memenuhi Kontrol 5.3",

        '/\bThe document content consists solely of timestamps and separators, containing absolutely no textual information, policy statements, or operational data\. Therefore, it cannot serve as primary operational evidence to demonstrate compliance with Control ([^\s]+) \(([^)]+)\), as it lacks all necessary factual components required for an audit assessment\b/i'
            => "Isi dokumen hanya terdiri dari penanda waktu dan pemisah, serta sama sekali tidak memuat informasi teks, kebijakan, atau data operasional. Oleh karena itu, dokumen tidak dapat berfungsi sebagai bukti operasional utama untuk menunjukkan kepatuhan terhadap Kontrol $1 ($2) karena tidak memiliki komponen faktual yang disyaratkan untuk penilaian audit",

        '/\bThe document appears generally related to the control requirement by listing network infrastructure components, but it does not provide evidence of how networks are secured, managed, or controlled\b/i'
            => "Dokumen ini secara umum memuat komponen infrastruktur jaringan, namun tidak menyediakan bukti bagaimana jaringan diamankan, dikelola, atau dikendalikan",
    ];

    foreach ($wholeClauses as $pattern => $replacement) {
        $t = preg_replace($pattern, $replacement, $t);
    }

    // Step 2: Component-level phrases & clauses
    $phraseReplacements = [
        '/\bDokumen ini SANGAT RELEVAN karena it is the primary operational artifact that directly satisfies the requirement of Control\b/i'
            => "Dokumen ini sangat relevan karena merupakan artefak operasional utama yang memenuhi persyaratan kontrol",
        '/\bDokumen ini SANGAT RELEVAN karena it is the primary operational artifact that directly satisfies the requirement\b/i'
            => "Dokumen ini sangat relevan karena merupakan artefak operasional utama yang memenuhi persyaratan",
        '/\bDokumen ini SANGAT RELEVAN karena it is a primary operational artifact\b/i'
            => "Dokumen ini sangat relevan karena merupakan artefak operasional utama",
        '/\bDokumen ini SANGAT RELEVAN karena it is the\b/i'
            => "Dokumen ini sangat relevan karena merupakan",
        '/\bDokumen ini SANGAT RELEVAN karena it is a\b/i'
            => "Dokumen ini sangat relevan karena merupakan",
        '/\bDokumen ini SANGAT RELEVAN karena it is\b/i'
            => "Dokumen ini sangat relevan karena merupakan",
        '/\bDokumen ini SANGAT RELEVAN karena it serves as a primary, detailed operational guideline \(([^)]+)\) that directly addresses the [\'"]how[\'"] and [\'"]what[\'"] of secure communication for a defined group \(([^)]+)\)\b/i'
            => "Dokumen ini sangat relevan karena berfungsi sebagai panduan operasional rinci ($1) yang secara langsung mengatur tata cara komunikasi aman bagi kelompok tertentu ($2)",
        '/\bDokumen ini SANGAT RELEVAN karena it serves as the primary operational guide for the CSIRT, which directly addresses the requirements of Control 7\.4\b/i'
            => "Dokumen ini sangat relevan karena berfungsi sebagai panduan operasional utama untuk CSIRT yang secara langsung memenuhi persyaratan Kontrol 7.4",
        '/\bDokumen ini SANGAT RELEVAN karena it serves as the primary, detailed operational procedure \(([^)]+)\) that governs the entire lifecycle of documented information\b/i'
            => "Dokumen ini sangat relevan karena berfungsi sebagai prosedur operasional rinci ($1) yang mengatur seluruh siklus hidup informasi terdokumentasi",
        '/\bDokumen ini SANGAT RELEVAN karena it serves as the foundational policy and framework \(([^)]+)\) for the entire Information Security Management System \(([^)]+)\) of the ([^\.]+)\b/i'
            => "Dokumen ini sangat relevan karena berfungsi sebagai kebijakan dan kerangka kerja dasar ($1) untuk seluruh Sistem Manajemen Keamanan Informasi ($2) dari $3",
        '/\bDokumen ini SANGAT RELEVAN karena it serves as\b/i'
            => "Dokumen ini sangat relevan karena berfungsi sebagai",
        '/\bDokumen ini SANGAT RELEVAN karena while it is\b/i'
            => "Dokumen ini sangat relevan karena meskipun merupakan",
        '/\bDokumen ini SANGAT RELEVAN karena\b/i'
            => "Dokumen ini sangat relevan karena",
        '/\bDokumen ini TIDAK RELEVAN untuk Control\b/i'
            => "Dokumen ini tidak relevan untuk kontrol",
        '/\bDokumen ini TIDAK RELEVAN karena\b/i'
            => "Dokumen ini tidak relevan karena",

        '/\bThis document is highly RELEVANT because it is the primary operational artifact that directly satisfies the requirement of Control ([^\s]+)\b/i'
            => "Dokumen ini sangat relevan karena merupakan artefak operasional utama yang secara langsung memenuhi persyaratan Kontrol $1",
        '/\bThis document is highly RELEVANT because it is\b/i'
            => "Dokumen ini sangat relevan karena merupakan",
        '/\bThis document is highly RELEVANT because it serves as\b/i'
            => "Dokumen ini sangat relevan karena berfungsi sebagai",
        '/\bThis document is highly RELEVANT because\b/i'
            => "Dokumen ini sangat relevan karena",
        '/\bThis document is highly relevant because\b/i'
            => "Dokumen ini sangat relevan karena",
        '/\bThe document is highly relevant because\b/i'
            => "Dokumen ini sangat relevan karena",
        '/\bThis document is highly relevant as it is\b/i'
            => "Dokumen ini sangat relevan karena merupakan",
        '/\bThe document is directly relevant to (control|clause) ([^\s]+) (as|because) it explicitly details\b/i'
            => "Dokumen ini relevan secara langsung dengan kontrol $2 karena secara eksplisit merinci",
        '/\bThe document is directly relevant to (control|clause) ([^\s]+) (as|because) it explicitly provides\b/i'
            => "Dokumen ini relevan secara langsung dengan kontrol $2 karena secara eksplisit menyediakan",
        '/\bThe document is directly relevant to (control|clause) ([^\s]+) (as|because) it provides\b/i'
            => "Dokumen ini relevan secara langsung dengan kontrol $2 karena menyediakan",
        '/\bThe document is directly relevant to (control|clause) ([^\s]+) (as|because) it contains\b/i'
            => "Dokumen ini relevan secara langsung dengan kontrol $2 karena memuat",
        '/\bThe document is directly relevant to (control|clause) ([^\s]+) (as|because) it\b/i'
            => "Dokumen ini relevan secara langsung dengan kontrol $2 karena",
        '/\bThe document is directly relevant to (control|clause) ([^\s]+)\b/i'
            => "Dokumen ini relevan secara langsung dengan kontrol $2",
        '/\bThe document is relevant to (control|clause) ([^\s]+) because it explicitly details\b/i'
            => "Dokumen ini relevan dengan kontrol $2 karena secara eksplisit merinci",
        '/\bThe document is relevant to (control|clause) ([^\s]+) because\b/i'
            => "Dokumen ini relevan dengan kontrol $2 karena",
        '/\bThe document is relevant because\b/i'
            => "Dokumen ini relevan karena",

        '/\bThe document is NOT_RELEVANT because it is a\b/i'
            => "Dokumen ini tidak relevan karena merupakan",
        '/\bThe document is NOT_RELEVANT because\b/i'
            => "Dokumen ini tidak relevan karena",
        '/\bThis document is NOT_RELEVANT because\b/i'
            => "Dokumen ini tidak relevan karena",
        '/\bThis document is not relevant because\b/i'
            => "Dokumen ini tidak relevan karena",
        '/\bThis image does not appear related to the (control requirement|control)\b/i'
            => "Gambar ini tidak tampak berkaitan dengan persyaratan kontrol",
        '/\bThis image appears directly related to (control|the control requirement)\b/i'
            => "Gambar ini tampak berkaitan langsung dengan kontrol",

        '/\bDokumen ini merupakan laporan audit yang merinci temuan dan tindakan perbaikan, bukan merupakan primary operational artifact like a Stakeholder Register or Needs Analysis Matrix\b/i'
            => "Dokumen ini merupakan laporan audit yang merinci temuan dan tindakan perbaikan, bukan merupakan artefak operasional utama seperti Register Pemangku Kepentingan atau Matriks Analisis Kebutuhan",
        '/\bWhile it addresses security controls that may be influenced by external expectations, it does not provide direct evidence of the systematic identification, documentation, or analysis of interested parties\' requirements as mandated by Clause 4\.2\b/i'
            => "Meskipun dokumen mencakup kontrol keamanan yang dapat dipengaruhi oleh ekspektasi eksternal, dokumen tidak menyediakan bukti langsung identifikasi, dokumentasi, atau analisis sistematis atas persyaratan pihak berkepentingan sebagaimana disyaratkan Klausul 4.2",

        '/\bDokumen ini merupakan resume pribadi \(CV\) dan tidak memuat informasi organisasi information, policies, or records related to the Information Security Management System \(ISMS\)\b/i'
            => "Dokumen ini merupakan resume pribadi (CV) dan tidak memuat informasi organisasi, kebijakan, maupun rekaman terkait Sistem Manajemen Keamanan Informasi (SMKI)",
        '/\bTherefore, it cannot serve as primary operational evidence to demonstrate that the organization has identified, documented, or analyzed the needs and expectations of interested parties as required by Control 4\.2\b/i'
            => "Oleh karena itu, dokumen ini tidak dapat berfungsi sebagai bukti operasional utama untuk menunjukkan bahwa organisasi telah mengidentifikasi, mendokumentasikan, atau menganalisis kebutuhan dan harapan pihak yang berkepentingan sesuai Kontrol 4.2",

        '/\bDokumen ini merupakan rencana proyek tingkat tinggi atau pernyataan ruang lingkup untuk SMKI implementation, not the primary operational artifact required for Control 5\.3\b/i'
            => "Dokumen ini merupakan rencana proyek tingkat tinggi atau pernyataan ruang lingkup implementasi SMKI, bukan artefak operasional utama yang dipersyaratkan untuk Kontrol 5.3",
        '/\bWhile it mentions the necessity of reviewing and fulfilling [\'"]Job Descriptions and Job Specifications[\'"] \(a key activity for 5\.3\), it does not provide the actual organizational chart, RACI matrix, or detailed role assignments that serve as direct, auditable evidence of assigned responsibilities and authorities\b/i'
            => "Meskipun dokumen menyebutkan keharusan meninjau uraian dan spesifikasi pekerjaan, dokumen ini tidak menyediakan struktur organisasi aktual, matriks RACI, atau penugasan peran rinci yang menjadi bukti langsung penugasan wewenang dan tanggung jawab",

        '/\bDokumen ini merupakan modul pelatihan teoritis yang menjelaskan persyaratan dari ISO 27001:2013, bukan being the organization\'s primary operational evidence \(such as a completed Context Analysis Report or Risk Register\)\b/i'
            => "Dokumen ini merupakan modul pelatihan teoritis yang menjelaskan persyaratan dari ISO 27001:2013, bukan merupakan bukti operasional utama organisasi (seperti Laporan Analisis Konteks atau Register Risiko)",
        '/\bWhile it thoroughly covers the methodology for addressing 6\.1\.1 \(Context and Risk\), it does not constitute the actual, executed planning artifact required for audit purposes\. Therefore, (it is|merupakan) marked as NOT_RELEVANT as direct operational evidence\b/i'
            => "Meskipun secara mendalam membahas metodologi untuk 6.1.1 (Konteks dan Risiko), dokumen ini bukan merupakan artefak perencanaan aktual yang telah dieksekusi untuk kebutuhan audit. Oleh karena itu, ditandai sebagai TIDAK RELEVAN sebagai bukti operasional langsung",

        '/\bDokumen ini menyediakan a detailed procedure for Incident Management, which is a critical operational control \(a risk treatment measure\)\. However, it does not contain the primary evidence of the initial planning process required by 6\.1\.1\b/i'
            => "Dokumen ini menyediakan prosedur rinci untuk Manajemen Insiden yang merupakan kontrol operasional penting (tindakan mitigasi risiko). Namun, dokumen ini tidak memuat bukti utama proses perencanaan awal yang disyaratkan oleh 6.1.1",
        '/\bSpecifically, it lacks the formal analysis \(such as a Context Analysis Report, PESTLE, or Stakeholder Register\) that integrates internal\/external issues \(4\.1\) and stakeholder needs \(4\.2\) to \*determine\* the initial risk and opportunity landscape\. Therefore, (it is|merupakan) NOT_RELEVANT as the primary operational evidence for 6\.1\.1\b/i'
            => "Khususnya, dokumen ini tidak memiliki analisis formal (seperti Laporan Analisis Konteks, PESTLE, atau Register Pemangku Kepentingan) yang mengintegrasikan isu internal/eksternal (4.1) dan kebutuhan pemangku kepentingan (4.2) untuk menentukan lanskap risiko dan peluang awal. Oleh karena itu, TIDAK RELEVAN sebagai bukti operasional utama untuk 6.1.1",

        '/\bDokumen ini tidak relevan karena merupakan Continual Improvement Register \(Clause 10\.1\), not the primary operational artifact for Risk Assessment \(Clause 6\.1\.2\)\b/i'
            => "Dokumen ini tidak relevan karena merupakan Register Peningkatan Berkelanjutan (Klausul 10.1), bukan artefak operasional utama untuk Penilaian Risiko (Klausul 6.1.2)",
        '/\bWhile the register \(CI-2024-02\) explicitly references the [\'"]Result of Risk Assessment 6\.1\.2,[\'"] the document itself does not contain the required detailed risk methodology, risk criteria \(impact\/likelihood scales\), or the comprehensive risk register necessary to satisfy the control requirement directly\b/i'
            => "Meskipun register (CI-2024-02) secara eksplisit mengacu pada Hasil Penilaian Risiko 6.1.2, dokumen ini sendiri tidak memuat metodologi risiko rinci, kriteria risiko (skala dampak/kemungkinan), atau register risiko komprehensif yang diperlukan untuk memenuhi persyaratan kontrol secara langsung",

        '/\bDokumen ini merupakan Laporan Penilaian Awal yang berisi temuan atau rekomendasi document, not the primary operational evidence required for Control 6\.2\b/i'
            => "Dokumen ini merupakan Laporan Penilaian Awal yang berisi temuan atau rekomendasi, bukan bukti operasional utama yang dipersyaratkan untuk Kontrol 6.2",
        '/\bWhile it repeatedly highlights the need to [\'"]Review the ISMS Policy and objectives[\'"] and [\'"]measure the effectiveness of controls,[\'"] it does not contain the documented, measurable objectives, the formal plan \(including PICs and deadlines\), or the KPI scorecard that directly satisfies the requirement of establishing and planning for information security objectives\b/i'
            => "Meskipun berulang kali menekankan perlunya meninjau Kebijakan dan sasaran SMKI serta mengukur efektivitas kontrol, dokumen ini tidak memuat sasaran terukur terdokumentasi, rencana formal (PIC dan tenggat waktu), atau kartu skor KPI yang memenuhi persyaratan penetapan dan perencanaan sasaran keamanan informasi",

        '/\bDokumen ini sangat relevan karena berfungsi sebagai the primary operational artifact defining the necessary competence requirements for personnel involved in cyber security training and operations\b/i'
            => "Dokumen ini sangat relevan karena berfungsi sebagai artefak operasional utama yang menetapkan persyaratan kompetensi bagi personel dalam pelatihan dan operasional keamanan siber",
        '/\bsecara eksplisit merinci the required educational background \(D3\/S1, S1\/D4\/S2\), specialized technical skills \(e\.g\., SIEM, Nmap, CTI\), and minimum professional experience \(3 years\) needed for both the trainees and the instructors, directly satisfying persyaratan dari ISO 27001:2022 Control 7\.2\b/i'
            => "Dokumen secara eksplisit merinci latar belakang pendidikan (D3/S1, S1/D4/S2), keahlian teknis khusus (seperti SIEM, Nmap, CTI), dan pengalaman profesional minimal (3 tahun) yang dibutuhkan untuk peserta maupun instruktur, sehingga secara langsung memenuhi persyaratan Kontrol 7.2 ISO 27001:2022",

        '/\bDokumen ini sangat relevan karena merupakan the primary operational artifact \(SOP\) that directly defines and controls the entire lifecycle of documented information\b/i'
            => "Dokumen ini sangat relevan karena merupakan artefak operasional utama (SOP) yang secara langsung mengatur seluruh siklus hidup informasi terdokumentasi",
        '/\bIt explicitly references ISO\/IEC 27001:2022 Clause 7\.5 and details the mandatory procedures for creation, approval, distribution, and disposal, thereby satisfying the core requirements of the control\b/i'
            => "Dokumen secara eksplisit mengacu pada Klausul 7.5 ISO/IEC 27001:2022 serta merinci prosedur wajib untuk pembuatan, persetujuan, distribusi, dan pemusnahan, sehingga memenuhi persyaratan inti kontrol",

        '/\bIt explicitly details the required unique identification structure, the mandatory metadata, the technical format standards, and the multi-stage review\/approval workflow, directly satisfying the requirements of ISO 27001:2022 Clause 7\.5\.2\b/i'
            => "Dokumen secara eksplisit merinci struktur identifikasi unik, metadata wajib, standar format teknis, dan alur peninjauan/persetujuan berjenjang, sehingga secara langsung memenuhi persyaratan Klausul 7.5.2 ISO 27001:2022",

        '/\bDokumen ini sangat relevan karena merupakan comprehensive, primary operational Standard Operating Procedure \(SOP\) explicitly designed to govern and satisfy persyaratan dari ISO\/IEC 27001:2022 Clause 7\.5\.3\b/i'
            => "Dokumen ini sangat relevan karena merupakan Standar Operasional Prosedur (SOP) utama yang dirancang komprehensif untuk mengatur dan memenuhi persyaratan Klausul 7.5.3 ISO/IEC 27001:2022",
        '/\bIt does not merely reference the control; it details the technical mechanisms \(AES-256, SHA-256\), procedural controls \(RBAC, Offboarding\), and governance structures \(Retention Schedules, External Review\) necessary to prove that documented information is controlled throughout its entire lifecycle, making it direct evidence\b/i'
            => "Dokumen tidak hanya sekadar mengacu pada kontrol, melainkan merinci mekanisme teknis (AES-256, SHA-256), kontrol prosedural (RBAC, Offboarding), dan tata kelola (Jadwal Retensi, Tinjauan Eksternal) yang membuktikan bahwa informasi terdokumentasi dikendalikan di sepanjang siklus hidupnya, sehingga menjadi bukti langsung",

        '/\bDokumen ini sangat relevan karena berfungsi sebagai the primary operational artifact \(SOP\) that dictates the [\'"]how-to[\'"] for executing the Information Security Management System \(ISMS\)\b/i'
            => "Dokumen ini sangat relevan karena berfungsi sebagai artefak operasional utama (SOP) yang memandu pelaksanaan teknis Sistem Manajemen Keamanan Informasi (SMKI)",
        '/\bsecara eksplisit merinci the operational controls, planning steps \(like data classification, access control implementation, and backup frequency\), and continuous monitoring requirements necessary to fulfill persyaratan dari Control 8\.1 \(Operational planning and control\)\. menyediakan bukti nyata mengenai the organization\'s planned and controlled processes\b/i'
            => "Dokumen secara eksplisit merinci kontrol operasional, langkah perencanaan (seperti klasifikasi data, akses kontrol, dan frekuensi cadangan), serta pemantauan berkelanjutan yang diperlukan untuk memenuhi persyaratan Kontrol 8.1 (Perencanaan dan pengendalian operasional), memberikan bukti nyata atas proses organisasi yang terencana dan terkendali",

        '/\bDokumen ini sangat relevan karena merupakan primary operational artifact—an Internal Audit Report—that directly addresses and provides objective evidence for Control 9\.2\.1 \(Internal Audit - General\)\b/i'
            => "Dokumen ini sangat relevan karena merupakan artefak operasional utama berupa Laporan Audit Internal yang secara langsung memberikan bukti objektif untuk Kontrol 9.2.1 (Audit Internal - Umum)",
        '/\bIt details the planned scope, the execution methodology \(sampling, technical checks\), the specific findings \(NCs\/OFIs\), and the mandated follow-up actions \(CAPA and Management Review input\), thereby satisfying the requirement to demonstrate routine, objective, and comprehensive internal auditing of the ISMS\b/i'
            => "Dokumen merinci ruang lingkup terencana, metodologi pelaksanaan (sampling, pengujian teknis), temuan spesifik (NC/OFI), dan tindak lanjut wajib (CAPA dan masukan Tinjauan Manajemen), sehingga memenuhi persyaratan pelaksanaan audit internal SMKI yang rutin, objektif, dan komprehensif",

        '/\bDokumen ini sangat relevan karena berfungsi sebagai the primary operational evidence \(the Audit Report\) that validates the execution and reporting phase of the internal audit program \(9\.2\.2\)\b/i'
            => "Dokumen ini sangat relevan karena berfungsi sebagai bukti operasional utama (Laporan Audit) yang memvalidasi tahap pelaksanaan dan pelaporan program audit internal (9.2.2)",
        '/\bAlthough merupakan not the formal [\'"]Audit Program[\'"] document, it proves that the organization planned, executed, and formally reported on a scheduled audit cycle \(Semester I 2024\), demonstrating compliance with the core requirements of establishing and maintaining the audit process\b/i'
            => "Meskipun bukan merupakan dokumen formal Program Audit, dokumen ini membuktikan bahwa organisasi telah merencanakan, melaksanakan, dan melaporkan siklus audit terjadwal (Semester I 2024), menunjukkan kepatuhan terhadap persyaratan penetapan dan pemeliharaan proses audit",

        '/\bDokumen ini sangat relevan karena meskipun merupakan an Internal Audit Programme \(Clause 9\.2\.2\) and not the Management Review Minutes \(Clause 9\.3\.1\) itself, berfungsi sebagai the primary operational evidence detailing the structured inputs required for the review\b/i'
            => "Dokumen ini sangat relevan karena meskipun merupakan Program Audit Internal (Klausul 9.2.2) dan bukan Notulen Tinjauan Manajemen (Klausul 9.3.1), dokumen berfungsi sebagai bukti operasional utama yang merinci masukan terstruktur yang disyaratkan untuk tinjauan manajemen",
        '/\bSpecifically, Section 3\.3 explicitly mandates that all audit summary reports, CAPA statuses, and nonconformity trends are mandatory inputs for the semi-annual Management Review \(RTM\) led by the Direktur Utama, thereby demonstrating the planned mechanism for top management to assess the ISMS\'s suitability and effectiveness\b/i'
            => "Secara khusus, Bagian 3.3 secara eksplisit mewajibkan bahwa semua laporan ringkasan audit, status CAPA, dan tren ketidaksesuaian adalah masukan wajib untuk Rapat Tinjauan Manajemen (RTM) semesteran yang dipimpin Direktur Utama, membuktikan mekanisme terencana bagi manajemen puncak untuk mengevaluasi efektivitas SMKI",

        '/\bDokumen ini relevan dengan kontrol A\.5\.20 karena it directly addresses the establishment of information security requirements within supplier agreements\b/i'
            => "Dokumen ini relevan dengan kontrol A.5.20 karena secara langsung mengatur penetapan persyaratan keamanan informasi dalam perjanjian pemasok",
        '/\bIt contains a specific implementation record table listing actual third-party vendors, their contracts, required security clauses, and attached NDAs, which serves as direct evidence of agreed security requirements\b/i'
            => "Dokumen memuat tabel catatan implementasi yang mencantumkan vendor pihak ketiga, kontrak mereka, klausul keamanan wajib, dan lampiran NDA, yang berfungsi sebagai bukti langsung atas kesepakatan persyaratan keamanan",

        '/\bIt specifies the technical controls \(encryption, VPNs\) and procedural steps \(classification, best practices\) required to manage internal and external communication needs, thereby providing direct evidence of the organization\'s established communication plan for the scope\b/i'
            => "Dokumen merinci kontrol teknis (enkripsi, VPN) dan langkah prosedural (klasifikasi, praktik terbaik) yang diperlukan untuk mengelola kebutuhan komunikasi internal dan eksternal, memberikan bukti langsung atas rencana komunikasi yang ditetapkan organisasi",

        '/\bIt explicitly details the communication needs \(what, when, with whom, and how\) by establishing specific protocols, such as using PGP encryption for sensitive data and defining communication channels for various stakeholders \(management, external law enforcement, etc\.\)\b/i'
            => "Dokumen secara eksplisit merinci kebutuhan komunikasi (apa, kapan, dengan siapa, dan bagaimana) dengan menetapkan protokol khusus, seperti penggunaan enkripsi PGP untuk data sensitif serta menentukan saluran komunikasi bagi berbagai pemangku kepentingan",

        // Catch general "The document is...", "This document is..."
        '/\b(The document|This document) is (an?|the)\b/i' => "Dokumen ini merupakan",
        '/\b(The document|This document) is\b/i' => "Dokumen ini merupakan",
        '/\b(The document|This document) provides\b/i' => "Dokumen ini menyediakan",
        '/\b(The document|This document) contains\b/i' => "Dokumen ini memuat",

        // General vocab and phrase cleanup
        '/\b(The document is|This document is) RELEVANT/i' => "Dokumen ini RELEVAN",
        '/\b(The document is|This document is) NOT_RELEVANT/i' => "Dokumen ini TIDAK RELEVAN",
        '/\bThis document is highly RELEVANT/i' => "Dokumen ini sangat RELEVAN",
        '/\bThe document is highly RELEVANT/i' => "Dokumen ini sangat RELEVAN",
        '/\bNOT_RELEVANT\b/' => "TIDAK RELEVAN",
        '/\bRELEVANT\b/' => "RELEVAN",
        '/\bthe primary(, formal)? operational (artifact|evidence|procedure|guideline)\b/i' => "artefak/bukti operasional utama",
        '/\ba primary(, formal)? operational (artifact|evidence|procedure|guideline)\b/i' => "artefak/bukti operasional utama",
        '/\bprimary operational artifact\b/i' => "artefak operasional utama",
        '/\bprimary operational evidence\b/i' => "bukti operasional utama",
        '/\boperational evidence\b/i' => "bukti operasional",
        '/\bdirect evidence\b/i' => "bukti langsung",
        '/\bdirect operational evidence\b/i' => "bukti operasional langsung",
        '/\bnot the primary operational artifact\b/i' => "bukan artefak operasional utama",
        '/\bnot a primary operational artifact\b/i' => "bukan artefak operasional utama",
        '/\bnot direct evidence\b/i' => "bukan bukti langsung",
        '/\bnot being the organization\'s\b/i' => "bukan merupakan milik organisasi",
        '/\bWhile it addresses\b/i' => "Meskipun mencakup",
        '/\bWhile it discusses\b/i' => "Meskipun membahas",
        '/\bWhile it mentions\b/i' => "Meskipun menyebutkan",
        '/\bWhile it thoroughly covers\b/i' => "Meskipun mencakup secara menyeluruh",
        '/\bWhile understanding stakeholders is\b/i' => "Meskipun memahami pemangku kepentingan merupakan",
        '/\bWhile the register\b/i' => "Meskipun register",
        '/\bWhile the document\b/i' => "Meskipun dokumen ini",
        '/\bit does not contain\b/i' => "dokumen ini tidak memuat",
        '/\bit does not provide\b/i' => "dokumen ini tidak menyediakan",
        '/\bit does not explicitly define\b/i' => "dokumen ini tidak secara eksplisit mendefinisikan",
        '/\bit lacks\b/i' => "dokumen ini tidak memiliki",
        '/\bTherefore, it cannot serve as\b/i' => "Oleh karena itu, dokumen ini tidak dapat berfungsi sebagai",
        '/\bTherefore, merupakan not\b/i' => "Oleh karena itu, dokumen ini bukan",
        '/\bTherefore, merupakan marked as\b/i' => "Oleh karena itu, ditandai sebagai",
        '/\bTherefore, it is\b/i' => "Oleh karena itu, dokumen ini",
        '/\bbecause it specifically details\b/i' => "karena secara spesifik merinci",
        '/\bbecause it specifically deals with\b/i' => "karena secara spesifik berkaitan dengan",
        '/\bbecause it provides\b/i' => "karena menyediakan",
        '/\bbecause it explicitly provides\b/i' => "karena secara eksplisit menyediakan",
        '/\bbecause it contains\b/i' => "karena memuat",
        '/\bbecause it is\b/i' => "karena merupakan",
        '/\bit is the\b/i' => "merupakan",
        '/\bit is a\b/i' => "merupakan",
        '/\bit is\b/i' => "merupakan",
        '/\bit serves as\b/i' => "berfungsi sebagai",
        '/\bit provides\b/i' => "menyediakan",
        '/\bit explicitly details\b/i' => "secara eksplisit merinci",
        '/\bit explicitly provides\b/i' => "secara eksplisit menyediakan",
        '/\bit explicitly contains\b/i' => "secara eksplisit memuat",
        '/\bsecara eksplisit merinci the\b/i' => "secara eksplisit merinci",
        '/\bsecara eksplisit menyediakan the\b/i' => "secara eksplisit menyediakan",
        '/\bwhich directly addresses\b/i' => "yang secara langsung memenuhi",
        '/\bdirectly satisfying\b/i' => "yang secara langsung memenuhi",
        '/\bdirectly satisfies\b/i' => "secara langsung memenuhi",
        '/\bpersyaratan dari\b/i' => "persyaratan dari",
        '/\bthe requirements of\b/i' => "persyaratan dari",
        '/\bthe requirement to\b/i' => "persyaratan untuk",
        '/\bthe requirement of\b/i' => "persyaratan dari",
        '/\brather than\b/i' => "bukan",
        '/\binstead of\b/i' => "bukan",
    ];

    foreach ($phraseReplacements as $pattern => $replacement) {
        $t = preg_replace($pattern, $replacement, $t);
    }

    return $t;
    }

    /**
     * Clean and translate relevance explanations to English.
     */
    public function translateRelevanceToEn(string $text, string $controlCode = '', string $status = ''): string
    {
        $t = trim($text);
        if ($t === '') return '';

        $isPureEn = preg_match('/\b(the document|this document|directly relevant|is not relevant)\b/i', $t)
                    && !preg_match('/\b(dokumen ini|karena|merupakan|tidak relevan)\b/i', $t);
        if ($isPureEn) return $t;

        $replacements = [
            '/\bDokumen ini (sangat )?relevan secara langsung dengan kontrol ([^\s]+) karena\b/i'
                => "This document is directly relevant to control $2 because",
            '/\bDokumen ini (sangat )?relevan karena\b/i'
                => "This document is highly relevant because",
            '/\bDokumen ini tidak relevan dengan kontrol ([^\s]+) karena\b/i'
                => "This document is not relevant to control $1 because",
            '/\bDokumen ini tidak relevan karena\b/i'
                => "This document is not relevant because",
            '/\bGambar ini tampak berkaitan langsung dengan\b/i'
                => "This image appears directly related to",
            '/\bGambar ini tidak tampak berkaitan dengan\b/i'
                => "This image does not appear related to",
        ];

        foreach ($replacements as $pattern => $replacement) {
            $t = preg_replace($pattern, $replacement, $t);
        }

        return $t;
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
