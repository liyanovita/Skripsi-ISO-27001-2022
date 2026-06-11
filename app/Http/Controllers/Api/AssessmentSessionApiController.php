<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Assessment\CreateSessionRequest;
use App\Http\Requests\Assessment\UpdateSessionRequest;
use App\Services\Assessment\SessionService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Schema(
 *     schema="AssessmentSession",
 *     type="object",
 *     title="Assessment Session",
 *     description="Assessment session model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Q2 2026 Internal Audit"),
 *     @OA\Property(property="status", type="string", enum={"draft", "in_progress", "completed"}, example="draft"),
 *     @OA\Property(property="overall_maturity_score", type="number", format="float", example=3.25),
 *     @OA\Property(property="ai_summary", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 * 
 * @OA\Schema(
 *     schema="CreateSessionRequest",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", minLength=3, maxLength=255, example="Q2 2026 Internal Audit")
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateSessionRequest",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", minLength=3, maxLength=255, example="Q2 2026 Internal Audit - Updated")
 * )
 */
class AssessmentSessionApiController extends BaseApiController
{
    public function __construct(
        protected SessionService $sessionService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/sessions",
     *     operationId="getAssessmentSessions",
     *     tags={"Assessment Sessions"},
     *     summary="Get all assessment sessions for authenticated user",
     *     description="Retrieve all assessment sessions belonging to the authenticated user, including session statistics",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sessions retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/AssessmentSession")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $sessions = $this->sessionService->getUserSessions(auth()->id());

        return $this->successResponse($sessions, 'Sessions retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/sessions",
     *     operationId="createAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Create a new assessment session",
     *     description="Create a new assessment session with ISO 27001:2022 standards initialized",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateSessionRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Session created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Assessment session created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AssessmentSession")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(CreateSessionRequest $request): JsonResponse
    {
        try {
            $session = $this->sessionService->createSession([
                'user_id' => auth()->id(),
                'name' => $request->name,
            ]);

            return $this->successResponse($session, 'Assessment session created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create session: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/sessions/{id}",
     *     operationId="getAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Get a specific assessment session",
     *     description="Retrieve a specific assessment session with its results and standards",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
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
     *             @OA\Property(property="message", type="string", example="Session retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AssessmentSession")
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
    public function show(int $id): JsonResponse
    {
        try {
            $session = $this->sessionService->getSession($id, auth()->id());
            $missing = $this->sessionService->getMissingScores($session);

            return $this->successResponse([
                'session' => $session,
                'missing_scores' => $missing,
            ], 'Session retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Session not found', 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/sessions/{id}",
     *     operationId="updateAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Update an assessment session",
     *     description="Update the name of an existing assessment session",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Session ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSessionRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Session updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AssessmentSession")
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
    public function update(UpdateSessionRequest $request, int $id): JsonResponse
    {
        try {
            $session = $this->sessionService->updateSession($id, auth()->id(), [
                'name' => $request->name
            ]);

            return $this->successResponse($session, 'Session updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update session: ' . $e->getMessage(), 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/sessions/{id}",
     *     operationId="deleteAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Delete an assessment session",
     *     description="Soft delete an assessment session (can be restored later)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Session ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Session deleted successfully")
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
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->sessionService->deleteSession($id, auth()->id());

            return $this->successResponse(null, 'Session deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete session: ' . $e->getMessage(), 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/sessions/{id}/clone",
     *     operationId="cloneAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Clone an assessment session",
     *     description="Create a copy of an existing assessment session with all its results",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Session ID to clone",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Session cloned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Session cloned successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AssessmentSession")
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
    public function clone(int $id): JsonResponse
    {
        try {
            $newSession = $this->sessionService->cloneSession($id, auth()->id());

            return $this->successResponse($newSession, 'Session cloned successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to clone session: ' . $e->getMessage(), 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/sessions/{id}/finalize",
     *     operationId="finalizeAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Finalize an assessment session",
     *     description="Mark an assessment session as completed after validating all requirements",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Session ID to finalize",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session finalized successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Session finalized successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AssessmentSession")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot finalize - missing requirements",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot finalize: 5/114 controls scored. Please score all controls first.")
     *         )
     *     )
     * )
     */
    public function finalize(int $id): JsonResponse
    {
        try {
            $session = $this->sessionService->finalizeSession($id, auth()->id());

            return $this->successResponse($session, 'Session finalized successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/sessions/{id}/restore",
     *     operationId="restoreAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Restore a deleted assessment session",
     *     description="Restore a previously soft-deleted assessment session",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Session ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Session restored successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Session not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to restore session")
     *         )
     *     )
     * )
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $this->sessionService->restoreSession($id, auth()->id());
            return $this->successResponse(null, 'Session restored successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to restore session: ' . $e->getMessage(), 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/sessions/{id}/force",
     *     operationId="forceDeleteAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Permanently delete an assessment session",
     *     description="Permanently delete an assessment session from the database",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Session ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session permanently deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Session permanently deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Session not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to permanently delete session")
     *         )
     *     )
     * )
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->sessionService->forceDeleteSession($id, auth()->id());
            return $this->successResponse(null, 'Session permanently deleted');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to permanently delete session: ' . $e->getMessage(), 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/sessions/{id}/export",
     *     operationId="exportAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Export assessment session to JSON",
     *     description="Export the entire assessment session and its results to a JSON format",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Session ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session exported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="app", type="string", example="OpenAudit-27001:2022"),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="exported_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Export failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to export session")
     *         )
     *     )
     * )
     */
    public function exportJson(int $id): JsonResponse
    {
        try {
            $exportData = $this->sessionService->exportSessionToJson($id);
            return response()->json($exportData, 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to export session: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/sessions/import",
     *     operationId="importAssessmentSession",
     *     tags={"Assessment Sessions"},
     *     summary="Import assessment session from JSON",
     *     description="Import an assessment session and its results from a JSON file",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="json_file", type="string", format="binary", description="JSON file containing exported session data"),
     *                 @OA\Property(property="new_name", type="string", nullable=true, description="Optional new name for the imported session")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Session imported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Session imported successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Import failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to import session")
     *         )
     *     )
     * )
     */
    public function importJson(\App\Http\Requests\Assessment\ImportSessionRequest $request): JsonResponse
    {
        try {
            $data = json_decode(file_get_contents($request->file('json_file')->getRealPath()), true);

            if (!$data) {
                return $this->errorResponse('Invalid JSON file format.', 400);
            }

            $session = $this->sessionService->importSessionFromJson(
                $data,
                auth()->id(),
                $request->new_name
            );

            return $this->successResponse($session, 'Session imported successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to import session: ' . $e->getMessage(), 500);
        }
    }
}
