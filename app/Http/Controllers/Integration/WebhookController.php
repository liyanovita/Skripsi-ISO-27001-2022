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
            $result = AssessmentResult::find($request->result_id);

            if (!$result) {
                throw ApiException::notFound('Assessment result not found');
            }

            $result->update([
                'ai_recommendation' => $request->ai_recommendation,
                'corrective_action_plan' => $request->action_plan,
                'control_insight' => $request->control_insight,
            ]);

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
        try {
            $session = AssessmentSession::find($request->session_id);

            if (!$session) {
                throw ApiException::notFound('Session not found');
            }

            $session->update([
                'ai_summary' => $request->summary,
            ]);

            return ApiResponse::success(
                null,
                'Executive summary updated successfully'
            );
        } catch (\Exception $e) {
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
