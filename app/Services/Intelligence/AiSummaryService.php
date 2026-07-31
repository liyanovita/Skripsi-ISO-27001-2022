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

        // Verify session access
        if (!$session->hasUserAccess()) {
            throw new \Exception('Unauthorized: You do not have permission to generate summary for this session.');
        }

        // Verify session status is completed
        if ($session->status !== 'completed') {
            throw new \Exception('You must finalize and complete this audit session before generating the AI summary.');
        }

        // Guard: block regenerate if session results data has not changed since last AI summary generation
        $currentHash = $this->computeSessionHash($session);
        $effectiveHash = $session->ai_summary_hash ?: ($session->ai_summary ? $currentHash : null);

        if ($session->ai_summary && $effectiveHash === $currentHash) {
            if (!$session->ai_summary_hash) {
                $session->update(['ai_summary_hash' => $currentHash]);
            }
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
                return [
                    'overall_assessment_summary' => $decoded['overall_assessment_conclusion'],
                    'control_insight'            => $decoded['overall_risk_areas'] ?? '',
                    'impact_interpretation'      => $decoded['assessment_confidence'] ?? '',
                    'strategic_recommendation'   => $decoded['executive_strategic_recommendations'] ?? [],
                    'action_plan'                => '',
                ];
            }
            if (isset($decoded['overall_assessment_summary'])) {
                return $decoded;
            }
        }

        // Legacy plain text — wrap it as summary only
        return [
            'overall_assessment_summary' => $rawSummary,
            'control_insight'            => '',
            'impact_interpretation'      => '',
            'strategic_recommendation'   => [],
            'action_plan'                => '',
        ];
    }

    public function receiveWebhook(array $data): AssessmentSession
    {
        \Illuminate\Support\Facades\Log::info("AiSummaryService: Webhook payload received", [
            'session_id' => $data['session_id'] ?? null,
            'keys_present' => array_keys($data),
        ]);

        $sessionId = $data['session_id'] ?? null;

        if (!$sessionId) {
            \Illuminate\Support\Facades\Log::error("AiSummaryService: Webhook processing failed - Missing session_id");
            throw new \Exception('Missing required data: session_id');
        }

        $session = AssessmentSession::find($sessionId);
        if (!$session) {
            \Illuminate\Support\Facades\Log::error("AiSummaryService: Webhook processing failed - Session not found", ['session_id' => $sessionId]);
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
                'overall_assessment_conclusion' => $data['overall_assessment_conclusion'] ?? '',
                'overall_risk_areas'            => $data['overall_risk_areas'] ?? '',
                'executive_strategic_recommendations' => $data['executive_strategic_recommendations'] ?? [],
                'assessment_confidence'         => $data['assessment_confidence'] ?? '',
            ];

            if (empty(trim($structured['overall_assessment_conclusion']))) {
                \Illuminate\Support\Facades\Log::error("AiSummaryService: Webhook processing failed - empty overall_assessment_conclusion", ['session_id' => $sessionId]);
                throw new \Exception('AI summary must contain overall_assessment_conclusion.');
            }

            $aiSummary = json_encode($structured, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif (
            isset($data['overall_assessment_summary']) ||
            isset($data['control_insight']) ||
            isset($data['impact_interpretation']) ||
            isset($data['strategic_recommendation']) ||
            isset($data['action_plan'])
        ) {
            $structured = [
                'overall_assessment_summary' => $data['overall_assessment_summary'] ?? '',
                'control_insight'            => $data['control_insight'] ?? '',
                'impact_interpretation'      => $data['impact_interpretation'] ?? '',
                'strategic_recommendation'   => $data['strategic_recommendation'] ?? [],
                'action_plan'                => $data['action_plan'] ?? '',
            ];

            if (empty(trim($structured['overall_assessment_summary']))) {
                \Illuminate\Support\Facades\Log::error("AiSummaryService: Webhook processing failed - empty overall_assessment_summary", ['session_id' => $sessionId]);
                throw new \Exception('AI summary must contain overall_assessment_summary.');
            }

            $aiSummary = json_encode($structured, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            // Fallback: legacy plain text key
            $aiSummary = $data['ai_summary'] ?? $data['summary'] ?? null;

            if (!$aiSummary) {
                \Illuminate\Support\Facades\Log::error("AiSummaryService: Webhook processing failed - empty summary payload", ['session_id' => $sessionId]);
                throw new \Exception('Missing required data: summary payload is empty.');
            }

            if (strlen(trim($aiSummary)) < 10) {
                \Illuminate\Support\Facades\Log::error("AiSummaryService: Webhook processing failed - summary too short", ['session_id' => $sessionId]);
                throw new \Exception('AI summary must be at least 10 characters long.');
            }
        }

        $session->update(['ai_summary' => $aiSummary]);
        Cache::forget("session_{$sessionId}_summary_status");

        \Illuminate\Support\Facades\Log::info("AiSummaryService: Webhook successfully committed to database", [
            'session_id' => $sessionId,
            'summary_length' => strlen($aiSummary),
        ]);

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
                'locale'       => app()->getLocale(),
            ]);

            if (!$response->successful()) {
                Log::error("N8N Summary returned HTTP {$response->status()}");
                throw new \Exception("Gagal menghubungi N8N (HTTP {$response->status()}). Pastikan N8N aktif dan URL webhook benar.");
            }
        } catch (\Exception $e) {
            Log::error("N8N Summary unavailable: {$e->getMessage()}");
            throw new \Exception("Koneksi ke N8N gagal: " . $e->getMessage());
        }
    }    protected function generateMockSummary(AssessmentSession $session): void
    {
        $score = $session->overall_maturity_score;
        $isId = app()->getLocale() === 'id';

        if ($score < 2) {
            if ($isId) {
                $structured = [
                    'overall_assessment_conclusion' => "Kematangan keamanan informasi organisasi saat ini berada pada tahap Awal/Ad-hoc. Sebagian besar kontrol belum ditentukan secara formal dan bergantung pada inisiatif individu alih-alih struktur tata kelola yang sistematis.",
                    'overall_risk_areas'            => "Area risiko utama meliputi ketiadaan Kebijakan Keamanan Informasi yang formal, peran dan tanggung jawab keamanan yang belum didefinisikan, serta dokumentasi bukti yang tidak memadai untuk kontrol dasar ISMS (Klausul 4-5).",
                    'assessment_confidence'         => "Celah ini memaparkan organisasi pada risiko tata kelola dan kepatuhan yang signifikan.",
                    'executive_strategic_recommendations' => [
                        "Menetapkan dan menyetujui secara formal Kebijakan Keamanan Informasi yang komprehensif yang disahkan oleh manajemen senior, sebagaimana diwajibkan dalam ISO/IEC 27001:2022 Klausul 5.",
                        "Mendefinisikan dan mengomunikasikan peran serta tanggung jawab keamanan di seluruh fungsi organisasi untuk menciptakan struktur akuntabilitas."
                    ],
                ];
            } else {
                $structured = [
                    'overall_assessment_conclusion' => "The organization's information security maturity is currently at the Initial/Ad-hoc stage. Most controls are not formally defined and rely on individual initiatives rather than systematic governance structures.",
                    'overall_risk_areas'            => "Primary risk areas include the absence of a formalized Information Security Policy, undefined security roles and responsibilities, and insufficient documentation of evidence for foundational ISMS controls (Clauses 4–5).",
                    'assessment_confidence'         => "These gaps expose the organization to significant governance and compliance risk.",
                    'executive_strategic_recommendations' => [
                        "Establish and formally approve a comprehensive Information Security Policy endorsed by senior management, as required under ISO/IEC 27001:2022 Clause 5.",
                        "Define and communicate security roles and responsibilities across all organizational functions to create accountability."
                    ],
                ];
            }
        } elseif ($score < 3.5) {
            if ($isId) {
                $structured = [
                    'overall_assessment_conclusion' => "Organisasi telah menetapkan kerangka kerja keamanan informasi dasar; namun, penerapannya masih belum konsisten di seluruh domain. Kematangan keseluruhan diklasifikasikan sebagai Berkembang.",
                    'overall_risk_areas'            => "Area risiko utama meliputi pelaksanaan audit internal berkala yang tidak konsisten, dokumentasi manajemen risiko yang tidak memadai untuk aset kritis, serta kelemahan dalam kontrol akses fisik dan logis.",
                    'assessment_confidence'         => "Hal ini mewakili risiko sedang hingga tinggi terhadap kelangsungan operasional dan kepatuhan regulasi.",
                    'executive_strategic_recommendations' => [
                        "Menerapkan program audit internal yang terstruktur dan terdokumentasi untuk memastikan evaluasi sistematis terhadap efektivitas ISMS.",
                        "Memformalkan proses manajemen risiko dengan daftar risiko, rencana penanganan, dan penerimaan risiko residual."
                    ],
                ];
            } else {
                $structured = [
                    'overall_assessment_conclusion' => "The organization has established a basic information security framework; however, implementation remains inconsistent across domains. The overall maturity is classified as Developing.",
                    'overall_risk_areas'            => "Key risk areas include inconsistent execution of periodic internal audits, insufficient risk management documentation for critical assets, and weaknesses in physical and logical access controls.",
                    'assessment_confidence'         => "These represent moderate-to-high risks to operational continuity and regulatory compliance.",
                    'executive_strategic_recommendations' => [
                        "Implement a structured and documented internal audit program to ensure systematic evaluation of ISMS effectiveness.",
                        "Formalize risk management processes with documented risk registers and treatment plans."
                    ],
                ];
            }
        } elseif ($score < 4.5) {
            if ($isId) {
                $structured = [
                    'overall_assessment_conclusion' => "Sistem manajemen keamanan informasi organisasi dikelola dengan baik dengan kontrol yang diterapkan dan dipantau secara berkala. Kematangan keseluruhan diklasifikasikan sebagai Terkelola.",
                    'overall_risk_areas'            => "Area risiko residual meliputi ketiadaan indikator kinerja terukur untuk kontrol utama, kedalaman yang terbatas dalam peninjauan manajemen terhadap insiden keamanan, dan peluang untuk memperkuat otomatisasi dalam pemantauan kontrol.",
                    'assessment_confidence'         => "Celah ini dapat memengaruhi kemampuan organisasi untuk menunjukkan peningkatan berkelanjutan.",
                    'executive_strategic_recommendations' => [
                        "Mengembangkan dan menerapkan Indikator Kinerja Utama (KPI) untuk mengukur efektivitas masing-masing kontrol.",
                        "Meningkatkan kedalaman dan formalitas peninjauan manajemen untuk mencakup analisis insiden secara sistematis."
                    ],
                ];
            } else {
                $structured = [
                    'overall_assessment_conclusion' => "The organization's information security management system is well-managed with controls that are implemented and monitored on a regular basis. The overall maturity is classified as Managed.",
                    'overall_risk_areas'            => "Residual risk areas include the absence of measurable performance indicators for key controls, limited depth in management review of security incidents, and opportunities to strengthen automation in control monitoring.",
                    'assessment_confidence'         => "These gaps may affect the organization's ability to demonstrate continuous improvement.",
                    'executive_strategic_recommendations' => [
                        "Develop and deploy Key Performance Indicators (KPIs) to measure the effectiveness of individual controls.",
                        "Enhance the depth and formality of management reviews to include systematic analysis of security incidents."
                    ],
                ];
            }
        } else {
            if ($isId) {
                $structured = [
                    'overall_assessment_conclusion' => "Organisasi telah mencapai tingkat kematangan keamanan informasi yang Dioptimalkan. Keamanan tertanam sebagai nilai inti organisasi, dan ISMS ditingkatkan terus-menerus.",
                    'overall_risk_areas'            => "Risiko utama berkaitan dengan mempertahankan konsistensi selama perubahan organisasi, memastikan kepatuhan rantai pasokan pihak ketiga, dan mempertahankan inovasi dalam lingkungan ancaman yang berkembang.",
                    'assessment_confidence'         => "Kelangsungan tata kelola selama transisi kepemimpinan harus dikelola secara eksplisit untuk mencegah kemunduran kontrol.",
                    'executive_strategic_recommendations' => [
                        "Mempertahankan dan memvalidasi tingkat kematangan yang tinggi melalui audit eksternal berkala.",
                        "Memperluas ruang lingkup ISMS untuk mengatasi kerangka kerja yang berkembang seperti ISO 27701 (Privasi) atau NIST CSF."
                    ],
                ];
            } else {
                $structured = [
                    'overall_assessment_conclusion' => "The organization has achieved an Optimized level of information security maturity. Security is embedded as a core organizational value, and the ISMS is continuously improved.",
                    'overall_risk_areas'            => "Principal risks relate to maintaining consistency during organizational changes, ensuring third-party supply chain compliance, and sustaining innovation in emerging threat environments.",
                    'assessment_confidence'         => "Governance continuity during leadership transitions should be explicitly managed to prevent control regression.",
                    'executive_strategic_recommendations' => [
                        "Sustain and validate high maturity levels through periodic external audits.",
                        "Expand the ISMS scope to address emerging frameworks such as ISO 27701 (Privacy) or NIST CSF."
                    ],
                ];
            }
        }

        $session->update(['ai_summary' => json_encode($structured, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        Cache::forget("session_{$session->id}_summary_status");
    }
}
