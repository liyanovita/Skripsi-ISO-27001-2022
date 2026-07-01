<?php

namespace App\Services\Intelligence;

use App\Models\AssessmentSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiSummaryService
{
    public function generate(int $sessionId): AssessmentSession
    {
        $session = AssessmentSession::with('results.standard')->findOrFail($sessionId);

        // Verify ownership
        if ($session->user_id !== auth()->id()) {
            throw new \Exception('Unauthorized: You do not have permission to generate summary for this session.');
        }

        // Verify session status is completed
        if ($session->status !== 'completed') {
            throw new \Exception('You must finalize and complete this audit session before generating the AI summary.');
        }

        // Guard: block regenerate if session results data has not changed since last AI summary generation
        $currentHash = $this->computeSessionHash($session);
        if ($session->ai_summary_hash && $session->ai_summary && $session->ai_summary_hash === $currentHash) {
            throw new \Exception('NO_DATA_CHANGE');
        }

        // Save hash snapshot before triggering (so repeated rapid clicks are also blocked)
        $session->update(['ai_summary_hash' => $currentHash]);

        // Track generation status in Cache instead of setting DB summary to null
        Cache::put("session_{$sessionId}_summary_status", 'processing', 300); // 5 minutes timeout

        $this->triggerN8nSummary($session);
        return $session->fresh();
    }

    /**
     * Compute a SHA-256 hash of the session's aggregated results data.
     * Only changes to maturity_rating or notes on any result should allow a regeneration.
     */
    public function computeSessionHash(AssessmentSession $session): string
    {
        $parts = $session->results
            ->sortBy('id')
            ->map(fn($r) => implode(':', [
                (string) $r->id,
                (string) $r->maturity_rating,
                $r->is_applicable ? '1' : '0',
                (string) ($r->notes ?? ''),
            ]))
            ->values()
            ->implode('|');

        return hash('sha256', $parts);
    }

    /**
     * Parse the ai_summary stored value (may be JSON string or plain text).
     * Returns an array with structured keys, or null if empty.
     */
    public static function parseSummary(?string $rawSummary): ?array
    {
        if (!$rawSummary) return null;

        // Attempt JSON decode
        $decoded = json_decode($rawSummary, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            if (isset($decoded['overall_assessment_conclusion'])) {
                return $decoded;
            }
        }

        // Legacy plain text — wrap it as conclusion only
        return [
            'overall_assessment_conclusion'       => $rawSummary,
            'overall_risk_areas'                  => null,
            'executive_strategic_recommendations' => [],
            'assessment_confidence'               => null,
        ];
    }

    public function receiveWebhook(array $data): AssessmentSession
    {
        $sessionId = $data['session_id'] ?? null;

        if (!$sessionId) {
            throw new \Exception('Missing required data: session_id');
        }

        $session = AssessmentSession::find($sessionId);
        if (!$session) {
            throw new \Exception('Session not found');
        }

        // Support new structured JSON format from n8n
        if (
            isset($data['overall_assessment_conclusion']) ||
            isset($data['overall_risk_areas']) ||
            isset($data['executive_strategic_recommendations']) ||
            isset($data['assessment_confidence'])
        ) {
            $structured = [
                'overall_assessment_conclusion'       => $data['overall_assessment_conclusion'] ?? '',
                'overall_risk_areas'                  => $data['overall_risk_areas'] ?? '',
                'executive_strategic_recommendations' => $data['executive_strategic_recommendations'] ?? [],
                'assessment_confidence'               => $data['assessment_confidence'] ?? '',
            ];

            if (empty(trim($structured['overall_assessment_conclusion']))) {
                throw new \Exception('AI summary must contain overall_assessment_conclusion.');
            }

            $aiSummary = json_encode($structured, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            // Fallback: legacy plain text key
            $aiSummary = $data['ai_summary'] ?? $data['summary'] ?? null;

            if (!$aiSummary) {
                throw new \Exception('Missing required data: summary payload is empty.');
            }

            if (strlen(trim($aiSummary)) < 10) {
                throw new \Exception('AI summary must be at least 10 characters long.');
            }
        }

        $session->update(['ai_summary' => $aiSummary]);
        Cache::forget("session_{$sessionId}_summary_status");

        return $session->fresh();
    }

    protected function triggerN8nSummary(AssessmentSession $session): void
    {
        $webhookUrl = config('services.n8n.webhook_summary_url');

        if (!$webhookUrl) {
            $this->generateMockSummary($session);
            return;
        }

        try {
            // Build enriched results payload for AI analysis
            $results = $session->results->map(fn($r) => [
                'code'              => $r->standard->code ?? '',
                'title'             => $r->standard->title ?? '',
                'description'       => $r->standard->description ?? '',
                'maturity_rating'   => $r->maturity_rating,
                'compliance_status' => $r->compliance_status,
                'notes'             => $r->notes ?? '',
                'evidence'          => is_array($r->evidence_file) ? implode(', ', $r->evidence_file) : ($r->evidence_file ?? ''),
            ]);

            $response = Http::timeout(60)->post($webhookUrl, [
                'session_id'   => $session->id,
                'session_name' => $session->name,
                'results'      => $results,
            ]);

            if (!$response->successful()) {
                Log::error("N8N Summary returned HTTP {$response->status()}");
                throw new \Exception("Gagal menghubungi N8N (HTTP {$response->status()}). Pastikan N8N aktif dan URL webhook benar.");
            }
        } catch (\Exception $e) {
            Log::error("N8N Summary unavailable: {$e->getMessage()}");
            throw new \Exception("Koneksi ke N8N gagal: " . $e->getMessage());
        }
    }

    protected function generateMockSummary(AssessmentSession $session): void
    {
        $score = $session->overall_maturity_score;

        if ($score < 2) {
            $structured = [
                'overall_assessment_conclusion'       => "The organization's information security maturity is currently at the Initial/Ad-hoc stage. Most controls are not formally defined and rely on individual initiatives rather than systematic governance structures.",
                'overall_risk_areas'                  => "Primary risk areas include the absence of a formalized Information Security Policy, undefined security roles and responsibilities, and insufficient documentation of evidence for foundational ISMS controls (Clauses 4–5). These gaps expose the organization to significant governance and compliance risk.",
                'executive_strategic_recommendations' => [
                    "Establish and formally approve a comprehensive Information Security Policy endorsed by senior management, as required under ISO/IEC 27001:2022 Clause 5.",
                    "Define and communicate security roles and responsibilities across all organizational functions to create accountability and governance structure.",
                    "Prioritize formalizing evidence documentation for Clauses 4 and 5 to support future internal and external audit readiness."
                ],
                'assessment_confidence'               => "Assessment confidence is limited as supporting notes and evidence are largely unavailable for most controls. The conclusions are based primarily on the reported Maturity Ratings and Compliance Statuses, and independent validation cannot be fully performed.",
            ];
        } elseif ($score < 3.5) {
            $structured = [
                'overall_assessment_conclusion'       => "The organization has established a basic information security framework; however, implementation remains inconsistent across domains. The overall maturity is classified as Developing, with observable gaps in operational consistency and evidence documentation.",
                'overall_risk_areas'                  => "Key risk areas include inconsistent execution of periodic internal audits, insufficient risk management documentation for critical assets, and weaknesses in physical and logical access controls. These represent moderate-to-high risks to operational continuity and regulatory compliance.",
                'executive_strategic_recommendations' => [
                    "Implement a structured and documented internal audit program to ensure systematic evaluation of ISMS effectiveness across all applicable controls.",
                    "Formalize risk management processes with documented risk registers, treatment plans, and residual risk acceptance for all critical information assets.",
                    "Strengthen physical security and logical access controls, particularly in domains covering asset management and access control (Annex A.7 and A.8)."
                ],
                'assessment_confidence'               => "Assessment confidence is partial. While some controls are supported by assessment notes, the absence of comprehensive evidence documentation across all domains limits the ability to independently validate implementation consistency.",
            ];
        } elseif ($score < 4.5) {
            $structured = [
                'overall_assessment_conclusion'       => "The organization's information security management system is well-managed with controls that are implemented and monitored on a regular basis. The overall maturity is classified as Managed, reflecting systematic governance and documented practices across most domains.",
                'overall_risk_areas'                  => "Residual risk areas include the absence of measurable performance indicators for key controls, limited depth in management review of security incidents, and opportunities to strengthen automation in control monitoring. These gaps may affect the organization's ability to demonstrate continuous improvement.",
                'executive_strategic_recommendations' => [
                    "Develop and deploy Key Performance Indicators (KPIs) to measure the effectiveness of individual controls and support evidence-based management reviews.",
                    "Enhance the depth and formality of management reviews to include systematic analysis of security incidents, audit findings, and corrective actions.",
                    "Explore automation solutions for real-time control monitoring and compliance reporting to reduce manual effort and improve detection capabilities."
                ],
                'assessment_confidence'               => "Assessment confidence is generally high. Supporting notes and maturity ratings are consistently available across assessed controls, and the overall conclusions are well-substantiated by the available assessment information.",
            ];
        } else {
            $structured = [
                'overall_assessment_conclusion'       => "The organization has achieved an Optimized level of information security maturity. Security is embedded as a core organizational value, and the ISMS is continuously improved through innovation, external benchmarking, and proactive threat management.",
                'overall_risk_areas'                  => "At this maturity level, principal risks relate to maintaining consistency during organizational changes, ensuring third-party supply chain compliance, and sustaining innovation in emerging threat environments. Governance continuity during leadership transitions should be explicitly managed.",
                'executive_strategic_recommendations' => [
                    "Sustain and validate high maturity levels through periodic external audits and independent third-party assessments to ensure continued certification readiness.",
                    "Expand the ISMS scope to address emerging frameworks such as ISO 27701 (Privacy) or NIST CSF to strengthen the organization's overall security posture.",
                    "Leverage the organization's maturity to contribute to industry-wide security knowledge sharing, strengthening both internal capability and external credibility."
                ],
                'assessment_confidence'               => "Assessment confidence is high. The assessment is comprehensively supported by documented evidence and detailed notes across all assessed controls, providing a strong foundation for the conclusions presented in this executive summary.",
            ];
        }

        $session->update(['ai_summary' => json_encode($structured, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        Cache::forget("session_{$session->id}_summary_status");
    }
}
