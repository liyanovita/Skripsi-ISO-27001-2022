<?php

namespace App\Http\Controllers\Intelligence;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Intelligence\AiSummaryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AiSummaryController extends Controller
{
    public function __construct(
        protected AiSummaryService $aiSummaryService
    ) {}

    public function generate(int $sessionId): JsonResponse
    {
        try {
            $this->aiSummaryService->generate($sessionId);

            return ApiResponse::success(
                ['status' => 'processing'],
                'AI summary generation triggered. Result will be available shortly via webhook.'
            );
        } catch (\Exception $e) {
            throw ApiException::internalError($e->getMessage());
        }
    }

    public function checkStatus(int $sessionId): JsonResponse
    {
        try {
            $session = \App\Models\AssessmentSession::findOrFail($sessionId);

            // Verify ownership
            if ($session->user_id !== auth()->id()) {
                throw ApiException::forbidden('Unauthorized: You do not have permission to access this session.');
            }

            $isProcessing = Cache::get("session_{$sessionId}_summary_status") === 'processing';

            if ($isProcessing) {
                return ApiResponse::success([
                    'status'     => 'processing',
                    'summary'    => null,
                    'summary_html' => null,
                    'structured' => null,
                ], 'AI summary is being generated.');
            }

            if ($session->ai_summary) {
                $parsed = AiSummaryService::parseSummary($session->ai_summary);

                // Build HTML from structured data
                $summaryHtml = $this->buildSummaryHtml($parsed);

                return ApiResponse::success([
                    'status'       => 'completed',
                    'summary'      => $session->ai_summary,
                    'summary_html' => $summaryHtml,
                    'structured'   => $parsed,
                ], 'Summary status retrieved.');
            }

            return ApiResponse::success([
                'status'       => 'idle',
                'summary'      => null,
                'summary_html' => null,
                'structured'   => null,
            ], 'No summary generated yet.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw ApiException::notFound('Session not found');
        } catch (\Exception $e) {
            throw ApiException::internalError($e->getMessage());
        }
    }

    public function receiveWebhook(Request $request): JsonResponse
    {
        try {
            $this->aiSummaryService->receiveWebhook($request->all());

            return ApiResponse::success(
                null,
                'AI Summary saved successfully'
            );
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Session not found') {
                throw ApiException::notFound('Session not found');
            }
            throw ApiException::internalError($e->getMessage());
        }
    }

    /**
     * Build a structured HTML block from the parsed summary array.
     */
    private function buildSummaryHtml(?array $parsed): ?string
    {
        if (!$parsed) return null;

        $html = '';

        if (!empty($parsed['overall_assessment_conclusion'])) {
            $html .= '<div class="summary-section">'
                   . '<div class="summary-section-title"><i class="fa-solid fa-chart-line"></i> Overall Assessment Conclusion</div>'
                   . '<p class="summary-section-body">' . e($parsed['overall_assessment_conclusion']) . '</p>'
                   . '</div>';
        }

        if (!empty($parsed['overall_risk_areas'])) {
            $html .= '<div class="summary-section">'
                   . '<div class="summary-section-title"><i class="fa-solid fa-triangle-exclamation"></i> Overall Risk Areas</div>'
                   . '<p class="summary-section-body">' . e($parsed['overall_risk_areas']) . '</p>'
                   . '</div>';
        }

        if (!empty($parsed['executive_strategic_recommendations'])) {
            $recs = $parsed['executive_strategic_recommendations'];
            if (is_string($recs)) $recs = [$recs];
            $html .= '<div class="summary-section">'
                   . '<div class="summary-section-title"><i class="fa-solid fa-bullseye"></i> Executive Strategic Recommendations</div>'
                   . '<ol class="summary-recs-list">';
            foreach ($recs as $rec) {
                $html .= '<li>' . e($rec) . '</li>';
            }
            $html .= '</ol></div>';
        }

        if (!empty($parsed['assessment_confidence'])) {
            $html .= '<div class="summary-section">'
                   . '<div class="summary-section-title"><i class="fa-solid fa-shield-check"></i> Assessment Confidence</div>'
                   . '<p class="summary-section-body">' . e($parsed['assessment_confidence']) . '</p>'
                   . '</div>';
        }

        return $html ?: null;
    }
}
