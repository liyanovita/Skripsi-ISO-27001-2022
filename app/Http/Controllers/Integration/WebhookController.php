<?php

namespace App\Http\Controllers\Integration;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\SendNotificationRequest;
use App\Http\Responses\ApiResponse;
use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Services\Notification\CapaReminderService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WebhookController extends Controller
{
    protected NotificationService $notificationService;
    protected CapaReminderService $capaReminderService;

    public function __construct(
        NotificationService $notificationService,
        CapaReminderService $capaReminderService
    ) {
        $this->notificationService = $notificationService;
        $this->capaReminderService = $capaReminderService;
    }

    /**
     * Handle n8n response after AI processing
     */
    public function handleN8nResponse(Request $request): JsonResponse
    {
        try {
            $success = app(\App\Services\Assessment\ResultService::class)->receiveN8nWebhook($request->all());

            if (!$success) {
                throw new \Exception('Failed to process AI recommendation');
            }

            $result = AssessmentResult::find($request->result_id);

            return ApiResponse::success(
                $result,
                'AI recommendation updated successfully'
            );
        } catch (\Exception $e) {
            throw ApiException::internalError($e->getMessage());
        }
    }

    /**
     * Handle session summary from n8n
     */
    public function handleSessionSummary(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::info("WebhookController (Web): handleSessionSummary called", [
            'ip' => $request->ip(),
            'payload_keys' => array_keys($request->all()),
        ]);

        try {
            app(\App\Services\Intelligence\AiSummaryService::class)->receiveWebhook($request->all());

            \Illuminate\Support\Facades\Log::info("WebhookController (Web): handleSessionSummary successfully completed");

            return ApiResponse::success(
                null,
                'Executive summary updated successfully'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("WebhookController (Web): handleSessionSummary failed: " . $e->getMessage());
            throw ApiException::internalError($e->getMessage());
        }
    }

    /**
     * Get reminders for CAPA tasks
     */
    public function getReminders(Request $request): JsonResponse
    {
        try {
            $tasks = $this->capaReminderService->dueTasks((int) $request->query('days', 3));

            return ApiResponse::success([
                'total' => $tasks->count(),
                'tasks' => $tasks,
            ], 'Reminders retrieved successfully');
        } catch (\Exception $e) {
            throw ApiException::internalError($e->getMessage());
        }
    }

    /**
     * Send notification via NotificationService
     */
    public function sendNotification(SendNotificationRequest $request): JsonResponse
    {
        try {
            $results = $this->notificationService->send(
                $request->channels,
                $request->template,
                $request->data
            );

            $hasSuccess = collect($results)->contains('success', true);
            $hasFailed = collect($results)->contains('success', false);

            $message = $hasSuccess
                ? ($hasFailed ? 'Partially sent' : 'Sent successfully to all channels')
                : 'Failed to send to all channels';

            return ApiResponse::success([
                'results' => $results,
            ], $message, $hasSuccess ? 200 : 500);
        } catch (\Exception $e) {
            throw ApiException::internalError($e->getMessage());
        }
    }
}
