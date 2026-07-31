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
            if ($e->getMessage() === 'NO_DATA_CHANGE') {
                return response()->json([
                    'success'   => false,
                    'no_change' => true,
                    'message'   => 'No data change detected.',
                ], 409);
            }
            throw ApiException::internalError($e->getMessage());
        }
    }

    public function checkStatus(int $sessionId): JsonResponse
    {
        try {
            $session = \App\Models\AssessmentSession::findOrFail($sessionId);

            // Verify session access
            if (!$session->hasUserAccess()) {
                throw ApiException::forbidden('Unauthorized: You do not have permission to access this session.');
            }

            $isProcessing = Cache::get("session_{$sessionId}_summary_status") === 'processing';

            if ($isProcessing) {
                return ApiResponse::success([
                    'status'       => 'processing',
                    'summary'      => null,
                    'summary_html' => null,
                    'structured'   => null,
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

        if (!empty($parsed['overall_assessment_summary'])) {
            $html .= '<div class="summary-section">'
                   . '<div class="summary-section-title"><i class="fa-solid fa-chart-line"></i> Overall Assessment Summary</div>'
                   . '<div class="summary-section-body">' . Str::markdown(e($parsed['overall_assessment_summary'])) . '</div>'
                   . '</div>';
        }

        if (!empty($parsed['control_insight'])) {
            $html .= '<div class="summary-section">'
                   . '<div class="summary-section-title"><i class="fa-solid fa-lightbulb"></i> Control Insight</div>'
                   . '<div class="summary-section-body">' . Str::markdown(e($parsed['control_insight'])) . '</div>'
                   . '</div>';
        }

        if (!empty($parsed['impact_interpretation'])) {
            $html .= '<div class="summary-section">'
                   . '<div class="summary-section-title"><i class="fa-solid fa-circle-nodes"></i> Impact Interpretation</div>'
                   . '<div class="summary-section-body">' . Str::markdown(e($parsed['impact_interpretation'])) . '</div>'
                   . '</div>';
        }

        if (!empty($parsed['strategic_recommendation'])) {
            $recs = $parsed['strategic_recommendation'];
            if (is_string($recs)) $recs = [$recs];
            $html .= '<div class="summary-section">'
                   . '<div class="summary-section-title"><i class="fa-solid fa-bullseye"></i> Strategic Recommendation</div>'
                   . '<ol class="summary-recs-list">';
            foreach ($recs as $rec) {
                $html .= '<li>' . Str::markdown(e($rec)) . '</li>';
            }
            $html .= '</ol></div>';
        }

        if (!empty($parsed['action_plan'])) {
            $html .= '<div class="summary-section">'
                   . '<div class="summary-section-title"><i class="fa-solid fa-circle-check"></i> Action Plan</div>'
                   . '<div class="summary-section-body">' . Str::markdown(e($parsed['action_plan'])) . '</div>'
                   . '</div>';
        }

        return $html ?: null;
    }
}
