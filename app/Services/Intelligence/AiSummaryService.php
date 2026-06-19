<?php

namespace App\Services\Intelligence;

use App\Models\AssessmentSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSummaryService
{
    public function generate(int $sessionId): AssessmentSession
    {
        $session = AssessmentSession::with('results')->findOrFail($sessionId);

        // Verify ownership
        if ($session->user_id !== auth()->id()) {
            throw new \Exception('Unauthorized: You do not have permission to generate summary for this session.');
        }

        // Clear existing summary to track new generation status
        $session->update(['ai_summary' => null]);

        $this->triggerN8nSummary($session);
        return $session->fresh();
    }

    public function receiveWebhook(array $data): AssessmentSession
    {
        $sessionId = $data['session_id'] ?? null;
        $aiSummary = $data['ai_summary'] ?? null;

        if (!$sessionId || !$aiSummary) {
            throw new \Exception('Missing required data: session_id and ai_summary');
        }

        // Validate ai_summary is not empty
        if (strlen(trim($aiSummary)) < 10) {
            throw new \Exception('AI summary must be at least 10 characters long.');
        }

        $session = AssessmentSession::find($sessionId);
        if (!$session) {
            throw new \Exception('Session not found');
        }

        $session->update(['ai_summary' => $aiSummary]);
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
            $response = Http::timeout(60)->post($webhookUrl, [
                'session_id'   => $session->id,
                'session_name' => $session->name,
                'results'      => $session->results->map(fn($r) => [
                    'code'   => $r->standard->code,
                    'rating' => $r->maturity_rating,
                    'status' => $r->compliance_status,
                ]),
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
            $summary = "CONCLUSION: Your current information security maturity level is at the 'Initial/Ad-hoc' stage. Most controls are not formally defined and rely on individual initiatives.\n\nRECOMMENDATION:\n1. Immediately establish a written Information Security Policy (ISMS).\n2. Define clear security roles and responsibilities across the organizational structure.\n3. Focus on formalizing evidence documentation for Clause 4 and 5 controls.";
        } elseif ($score < 3.5) {
            $summary = "CONCLUSION: The organization has a basic framework, but implementation is still inconsistent ('Developing'). Operational evidence documentation remains the main gap.\n\nRECOMMENDATION:\n1. Improve the consistency of periodic internal audits.\n2. Conduct more documented risk management for critical assets.\n3. Strengthen physical and logical controls in Domains A.7 and A.8.";
        } elseif ($score < 4.5) {
            $summary = "CONCLUSION: Your information security management system is well 'Managed'. Controls are implemented and monitored regularly.\n\nRECOMMENDATION:\n1. Implement performance metrics (KPIs) to measure the effectiveness of each control.\n2. Conduct deeper management reviews of security incidents.\n3. Start exploring automation for real-time control monitoring.";
        } else {
            $summary = "CONCLUSION: Your organization is at the 'Optimized' level. Security has become a corporate culture and is continuously improved through innovation.\n\nRECOMMENDATION:\n1. Maintain standards through periodic external audits.\n2. Benchmark against other international standards (such as NIST or ISO 27701).\n3. Share security best practices within your industry ecosystem.";
        }

        $session->update(['ai_summary' => $summary]);
    }
}
