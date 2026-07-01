<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Assessment\UpdateResultRequest;
use App\Services\Assessment\ResultService;
use App\Services\Assessment\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="AssessmentResult",
 *     type="object",
 *     title="Assessment Result",
 *     description="Assessment result for a specific ISO 27001:2022 control",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="session_id", type="integer", example=1),
 *     @OA\Property(property="standard_id", type="integer", example=1),
 *     @OA\Property(property="maturity_rating", type="integer", minimum=0, maximum=5, example=3),
 *     @OA\Property(property="compliance_status", type="string", enum={"compliant", "non_compliant", "partially_compliant", "not_assessed"}, example="compliant"),
 *     @OA\Property(property="risk_level", type="string", enum={"low", "medium", "high", "critical"}, example="medium"),
 *     @OA\Property(property="evidence_description", type="string", nullable=true),
 *     @OA\Property(property="evidence_file_path", type="string", nullable=true),
 *     @OA\Property(property="ai_recommendation", type="string", nullable=true),
 *     @OA\Property(property="corrective_action_plan", type="string", nullable=true),
 *     @OA\Property(property="control_insight", type="string", nullable=true),
 *     @OA\Property(property="risk_priority", type="string", nullable=true),
 *     @OA\Property(property="evidence_validation", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateResultRequest",
 *     type="object",
 *     @OA\Property(property="maturity_rating", type="integer", minimum=0, maximum=5, example=3),
 *     @OA\Property(property="compliance_status", type="string", enum={"compliant", "non_compliant", "partially_compliant", "not_assessed"}, example="compliant"),
 *     @OA\Property(property="risk_level", type="string", enum={"low", "medium", "high", "critical"}, example="medium"),
 *     @OA\Property(property="evidence_description", type="string", nullable=true, example="Implemented access control policy with regular reviews"),
 *     @OA\Property(property="evidence_file", type="string", format="binary", nullable=true, description="Evidence file upload")
 * )
 * 
 * @OA\Schema(
 *     schema="AiInsightResponse",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="has_ai", type="boolean", example=true),
 *     @OA\Property(property="ai_recommendation", type="string", nullable=true),
 *     @OA\Property(property="corrective_action_plan", type="string", nullable=true),
 *     @OA\Property(property="control_insight", type="string", nullable=true),
 *     @OA\Property(property="risk_priority", type="string", nullable=true),
 *     @OA\Property(property="evidence_validation", type="string", nullable=true)
 * )
 */
class AssessmentResultApiController extends BaseApiController
{
    public function __construct(
        protected ResultService $resultService,
        protected SessionService $sessionService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/sessions/{sessionId}/results",
     *     operationId="getSessionResults",
     *     tags={"Assessment Results"},
     *     summary="Get all results for a session",
     *     description="Retrieve all assessment results for a specific session with missing score information",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         required=true,
     *         description="Session ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Session results retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="session",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(
     *                         property="results",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/AssessmentResult")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="missing_scores",
     *                     type="object",
     *                     @OA\Property(property="count", type="integer", example=5),
     *                     @OA\Property(
     *                         property="codes",
     *                         type="array",
     *                         @OA\Items(type="string", example="A.5.1.1")
     *                     )
     *                 )
     *             )
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
    public function index(int $sessionId): JsonResponse
    {
        try {
            $session = $this->sessionService->getSession($sessionId, auth()->id());
            $missing = $this->sessionService->getMissingScores($session);

            return $this->successResponse([
                'session' => $session,
                'missing_scores' => $missing,
            ], 'Session results retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Session not found', 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/results/{id}",
     *     operationId="updateAssessmentResult",
     *     tags={"Assessment Results"},
     *     summary="Update an assessment result",
     *     description="Update assessment result with maturity rating, compliance status, and evidence",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Result ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateResultRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Result updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Assessment for A.5.1.1 successfully saved."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="maturity_rating", type="integer", example=3),
     *                 @OA\Property(property="compliance_status", type="string", example="compliant"),
     *                 @OA\Property(property="risk_level", type="string", example="medium")
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
     *                     property="maturity_rating",
     *                     type="array",
     *                     @OA\Items(type="string", example="The maturity rating must be between 0 and 5.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateResultRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->resultService->updateResult(
                $id,
                $request->all(),
                $request->file('evidence_file')
            );

            return $this->successResponse([
                'id' => $result->id,
                'maturity_rating' => $result->maturity_rating,
                'compliance_status' => $result->compliance_status,
                'risk_level' => $result->risk_level,
                'status' => $result->status,
            ], 'Assessment for ' . $result->standard->code . ' successfully saved.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update result: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/results/{id}/ai-insight",
     *     operationId="generateAiInsight",
     *     tags={"Assessment Results"},
     *     summary="Generate AI insight for result",
     *     description="Trigger AI analysis to generate recommendations and insights for an assessment result",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Result ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI insight generation triggered",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="AI insight generation triggered successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="ai_recommendation", type="string", nullable=true, example=null)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Result not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Assessment result not found")
     *         )
     *     )
     * )
     */
    public function generateAiInsight(int $id): JsonResponse
    {
        try {
            $this->resultService->generateAiInsight($id);

            return $this->successResponse(
                ['ai_recommendation' => null],
                'AI insight generation triggered successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate AI insight: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/results/{id}/ai-status",
     *     operationId="checkAiStatus",
     *     tags={"Assessment Results"},
     *     summary="Check AI insight status",
     *     description="Check if AI insights are available for an assessment result and retrieve them",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Result ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="AI status retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AiInsightResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Result not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Assessment result not found")
     *         )
     *     )
     * )
     */
    public function checkAiStatus(int $id): JsonResponse
    {
        try {
            $result = $this->resultService->getResultById($id);

            return $this->successResponse([
                'id' => $result->id,
                'has_ai' => !empty($result->ai_recommendation),
                'ai_recommendation' => $result->ai_recommendation,
                'corrective_action_plan' => $result->corrective_action_plan,
                'control_insight' => $result->control_insight,
                'risk_priority' => $result->risk_priority,
                'evidence_validation' => $result->evidence_validation,
                'impact_interpretation' => $result->impact_interpretation
            ], 'AI status retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Assessment result not found', 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/webhook/n8n/ai-recommendation",
     *     operationId="receiveN8nWebhook",
     *     tags={"Assessment Results"},
     *     summary="Receive AI recommendation from N8N webhook",
     *     description="Webhook endpoint to receive AI-generated recommendations from N8N workflow",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="result_id", type="integer", example=1),
     *             @OA\Property(property="ai_recommendation", type="string", example="Implement multi-factor authentication"),
     *             @OA\Property(property="corrective_action_plan", type="string", nullable=true),
     *             @OA\Property(property="control_insight", type="string", nullable=true),
     *             @OA\Property(property="risk_priority", type="string", nullable=true),
     *             @OA\Property(property="evidence_validation", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI recommendation saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="AI Recommendation saved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Result not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Assessment result not found")
     *         )
     *     )
     * )
     */
    public function receiveN8nWebhook(Request $request): JsonResponse
    {
        try {
            $this->resultService->receiveN8nWebhook($request->all());

            return $this->successResponse(
                null,
                'AI Recommendation saved successfully'
            );
        } catch (\Exception $e) {
            if ($e->getMessage() === 'AssessmentResult not found') {
                return $this->errorResponse('Assessment result not found', 404);
            }
            return $this->errorResponse('Failed to save AI recommendation: ' . $e->getMessage(), 500);
        }
    }
}
