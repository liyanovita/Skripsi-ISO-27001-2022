<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Integration\SendNotificationRequest;
use App\Services\Notification\CapaReminderService;
use App\Services\Notification\NotificationService;
use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Schema(
 *     schema="ReminderTask",
 *     type="object",
 *     title="Reminder Task",
 *     description="CAPA task reminder information",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="pic", type="string", example="John Doe"),
 *     @OA\Property(property="due_date", type="string", format="date", example="2026-12-31"),
 *     @OA\Property(property="is_overdue", type="boolean", example=false),
 *     @OA\Property(property="days_left", type="integer", example=5),
 *     @OA\Property(property="control_code", type="string", example="A.5.1.1"),
 *     @OA\Property(property="control_title", type="string", example="Information security policy"),
 *     @OA\Property(property="session_name", type="string", example="Q2 2026 Assessment")
 * )
 * 
 * @OA\Schema(
 *     schema="SendNotificationRequest",
 *     type="object",
 *     required={"channels", "template", "data"},
 *     @OA\Property(
 *         property="channels",
 *         type="array",
 *         @OA\Items(type="string", enum={"telegram"}),
 *         example={"telegram"}
 *     ),
 *     @OA\Property(property="template", type="string", enum={"reminder", "completion"}, example="reminder"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="message", type="string", example="Assessment reminder notification"),
 *         @OA\Property(property="session_name", type="string", example="Q2 2026 Assessment"),
 *         @OA\Property(property="due_date", type="string", format="date", example="2026-12-31")
 *     )
 * )
 */
class WebhookApiController extends BaseApiController
{
    public function __construct(
        protected NotificationService $notificationService,
        protected CapaReminderService $capaReminderService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/webhook/n8n/ai-response",
     *     operationId="handleN8nAiResponse",
     *     tags={"Webhooks"},
     *     summary="Handle N8N AI response webhook",
     *     description="Webhook endpoint to receive AI processing results from N8N workflow",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="result_id", type="integer", example=1),
     *             @OA\Property(property="recommendation", type="string", example="Implement multi-factor authentication"),
     *             @OA\Property(property="action_plan", type="string", nullable=true, example="1. Deploy MFA solution 2. Train users"),
     *             @OA\Property(property="priority", type="string", nullable=true, example="High"),
     *             @OA\Property(property="insight", type="string", nullable=true, example="Critical security control")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI recommendation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="AI recommendation updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assessment result not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Assessment result not found")
     *         )
     *     )
     * )
     */
    public function handleN8nResponse(Request $request): JsonResponse
    {
        try {
            $success = app(\App\Services\Assessment\ResultService::class)->receiveN8nWebhook($request->all());

            if (!$success) {
                return $this->errorResponse('Failed to process AI recommendation', 500);
            }

            $result = AssessmentResult::find($request->result_id);

            return $this->successResponse(
                $result,
                'AI recommendation updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update AI recommendation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/webhook/n8n/session-summary",
     *     operationId="handleSessionSummary",
     *     tags={"Webhooks"},
     *     summary="Handle session summary webhook",
     *     description="Webhook endpoint to receive executive summary from N8N workflow",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="session_id", type="integer", example=1),
     *             @OA\Property(property="summary", type="string", example="The assessment reveals moderate maturity with key areas for improvement...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Executive summary updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Executive summary updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Session not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Session not found")
     *         )
     *     )
     * )
     */
    public function handleSessionSummary(Request $request): JsonResponse
    {
        try {
            app(\App\Services\Intelligence\AiSummaryService::class)->receiveWebhook($request->all());

            return $this->successResponse(
                null,
                'Executive summary updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update executive summary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/webhook/reminders",
     *     operationId="getReminders",
     *     tags={"Webhooks"},
     *     summary="Get CAPA task reminders",
     *     description="Retrieve upcoming and overdue CAPA tasks for reminder notifications",
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         required=false,
     *         description="Number of days ahead to check for due tasks",
     *         @OA\Schema(type="integer", default=3, example=7)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reminders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reminders retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=5),
     *                 @OA\Property(
     *                     property="tasks",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ReminderTask")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getReminders(Request $request): JsonResponse
    {
        try {
            $tasks = $this->capaReminderService->dueTasks((int) $request->query('days', 3));

            return $this->successResponse([
                'total' => $tasks->count(),
                'tasks' => $tasks,
            ], 'Reminders retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve reminders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/webhook/send-notification",
     *     operationId="sendNotification",
     *     tags={"Webhooks"},
     *     summary="Send notification via configured channels",
     *     description="Send notifications through configured channels (Telegram, etc.) using templates",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SendNotificationRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sent successfully to all channels"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="results",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="channel", type="string", example="telegram"),
     *                         @OA\Property(property="success", type="boolean", example=true),
     *                         @OA\Property(property="message", type="string", example="Message sent successfully")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="channels",
     *                     type="array",
     *                     @OA\Items(type="string", example="The channels field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
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

            return $this->successResponse([
                'results' => $results,
            ], $message, $hasSuccess ? 200 : 500);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send notification: ' . $e->getMessage(), 500);
        }
    }
}
