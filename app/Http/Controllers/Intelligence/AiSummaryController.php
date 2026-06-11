<?php

namespace App\Http\Controllers\Intelligence;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Intelligence\AiSummaryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiSummaryController extends Controller
{
    public function __construct(
        protected AiSummaryService $aiSummaryService
    ) {}

    public function generate(int $sessionId): JsonResponse
    {
        try {
            // Trigger generation asynchronously (pings n8n/Ollama in background).
            // The actual result is written back to the DB via the receiveWebhook endpoint
            // called by n8n — no blocking polling needed here.
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

            return ApiResponse::success([
                'status' => $session->ai_summary ? 'completed' : 'processing',
                'summary' => $session->ai_summary,
                'summary_html' => $session->ai_summary ? \Illuminate\Support\Str::markdown(e($session->ai_summary)) : null,
            ], 'Summary status retrieved.');
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
}
