<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Compliance\UpdateWorkspaceEntryRequest;
use App\Services\Compliance\WorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Schema(
 *     schema="WorkspaceEntry",
 *     type="object",
 *     title="Workspace Entry",
 *     description="Statement of Applicability workspace entry",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="session_id", type="integer", example=1),
 *     @OA\Property(property="standard_id", type="integer", example=1),
 *     @OA\Property(property="is_applicable", type="boolean", example=true),
 *     @OA\Property(property="soa_justification", type="string", nullable=true, example="Required for financial data protection"),
 *     @OA\Property(property="treatment_due_date", type="string", format="date", nullable=true, example="2026-12-31"),
 *     @OA\Property(property="treatment_pic", type="string", nullable=true, example="John Doe"),
 *     @OA\Property(property="treatment_status", type="string", enum={"open", "in_progress", "closed"}, example="in_progress"),
 *     @OA\Property(
 *         property="standard",
 *         type="object",
 *         @OA\Property(property="code", type="string", example="A.5.1.1"),
 *         @OA\Property(property="title", type="string", example="Information security policy"),
 *         @OA\Property(property="type", type="string", example="control")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="WorkspaceData",
 *     type="object",
 *     title="Workspace Data",
 *     description="Complete workspace data for Statement of Applicability",
 *     @OA\Property(
 *         property="sessions",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/AssessmentSession")
 *     ),
 *     @OA\Property(
 *         property="selectedSession",
 *         nullable=true,
 *         ref="#/components/schemas/AssessmentSession"
 *     ),
 *     @OA\Property(
 *         property="selectedId",
 *         type="integer",
 *         nullable=true,
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="results",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/WorkspaceEntry")
 *     ),
 *     @OA\Property(
 *         property="stats",
 *         type="object",
 *         @OA\Property(property="total", type="integer", example=122),
 *         @OA\Property(property="gaps", type="integer", example=12),
 *         @OA\Property(property="applicable", type="integer", example=117),
 *         @OA\Property(property="not_applicable", type="integer", example=5),
 *         @OA\Property(property="closed", type="integer", example=4)
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateWorkspaceEntryRequest",
 *     type="object",
 *     @OA\Property(property="is_applicable", type="boolean", example=true),
 *     @OA\Property(property="soa_justification", type="string", nullable=true, example="Required for financial data protection"),
 *     @OA\Property(property="treatment_due_date", type="string", format="date", nullable=true, example="2026-12-31"),
 *     @OA\Property(property="treatment_pic", type="string", nullable=true, example="John Doe"),
 *     @OA\Property(property="treatment_status", type="string", enum={"open", "in_progress", "closed"}, example="in_progress")
 * )
 */
class ComplianceApiController extends BaseApiController
{
    public function __construct(
        protected WorkspaceService $workspaceService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/compliance/workspace",
     *     operationId="getWorkspaceData",
     *     tags={"Compliance Workspace"},
     *     summary="Get Statement of Applicability workspace data",
     *     description="Retrieve complete workspace data for Statement of Applicability management",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="session_id",
     *         in="query",
     *         required=false,
     *         description="Filter by specific session ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Workspace data retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/WorkspaceData")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->workspaceService->getWorkspaceData(
            auth()->id(),
            $request->get('session_id')
        );

        return $this->successResponse($data, 'Workspace data retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/compliance/workspace/{resultId}",
     *     operationId="updateWorkspaceEntry",
     *     tags={"Compliance Workspace"},
     *     summary="Update a workspace entry",
     *     description="Update Statement of Applicability entry with justification and treatment information",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="resultId",
     *         in="path",
     *         required=true,
     *         description="Assessment Result ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateWorkspaceEntryRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Entry updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Record updated successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="is_applicable", type="boolean", example=true),
     *                 @OA\Property(property="soa_justification", type="string", example="Required for financial data protection"),
     *                 @OA\Property(property="treatment_due_date", type="string", format="date", example="2026-12-31"),
     *                 @OA\Property(property="treatment_pic", type="string", example="John Doe"),
     *                 @OA\Property(property="treatment_status", type="string", example="in_progress")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Entry not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Workspace entry not found")
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
     *                     property="treatment_status",
     *                     type="array",
     *                     @OA\Items(type="string", example="The treatment status must be one of: open, in_progress, closed.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function updateEntry(UpdateWorkspaceEntryRequest $request, int $resultId): JsonResponse
    {
        try {
            $result = $this->workspaceService->updateEntry(
                $resultId,
                auth()->id(),
                $request->all()
            );

            return $this->successResponse([
                'is_applicable' => $result->is_applicable,
                'soa_justification' => $result->soa_justification,
                'treatment_due_date' => $result->treatment_due_date,
                'treatment_pic' => $result->treatment_pic,
                'treatment_status' => $result->treatment_status,
            ], 'Record updated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update entry: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/compliance/soa/{sessionId}/excel",
     *     operationId="exportSoaExcel",
     *     tags={"Compliance Workspace"},
     *     summary="Export Statement of Applicability as Excel",
     *     description="Generate and download Statement of Applicability as Excel spreadsheet",
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
     *         description="SoA Excel generated successfully",
     *         @OA\MediaType(
     *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *             @OA\Schema(type="string", format="binary")
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
    public function exportSoaExcel(int $sessionId): BinaryFileResponse|JsonResponse
    {
        try {
            $session = $this->workspaceService->getSoaData($sessionId, auth()->id());
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\SoaExport($sessionId),
                "SoA_ISO27001:2022_{$session->name}.xlsx"
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export SoA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/compliance/soa/{sessionId}/pdf",
     *     operationId="exportSoaPdf",
     *     tags={"Compliance Workspace"},
     *     summary="Export Statement of Applicability as PDF",
     *     description="Generate and download Statement of Applicability as PDF document",
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
     *         description="SoA PDF generated successfully",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
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
    public function exportSoaPdf(int $sessionId): Response|JsonResponse
    {
        try {
            $session = $this->workspaceService->getSoaData($sessionId, auth()->id());

            $orderKey = fn($result) => sprintf(
                '%s|%s',
                $result->standard->parent?->code ?? '',
                $result->standard->code ?? ''
            );

            $clausaResults = $session->results
                ->filter(fn($r) => $this->isExportableResult($r) && $r->standard->type === 'clausa')
                ->sortBy($orderKey, SORT_NATURAL | SORT_FLAG_CASE)
                ->values();

            $annexResults = $session->results
                ->filter(fn($r) => $this->isExportableResult($r) && $r->standard->type === 'control')
                ->sortBy($orderKey, SORT_NATURAL | SORT_FLAG_CASE)
                ->values();

            if ($clausaResults->isEmpty() && $annexResults->isEmpty()) {
                $annexResults = $session->results
                    ->filter(fn($r) => $this->isExportableResult($r))
                    ->sortBy($orderKey, SORT_NATURAL | SORT_FLAG_CASE)
                    ->values();
            }

            $pdf = Pdf::loadView('pages.workspace.soa_pdf', [
                'session'       => $session,
                'clausaResults' => $clausaResults,
                'annexResults'  => $annexResults,
                'date'          => now()->format('d F Y'),
            ]);

            return $pdf->download("SoA_ISO27001:2022_{$session->name}.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export SoA PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function isExportableResult($result): bool
    {
        return $result->standard
            && is_array($result->standard->questions)
            && count($result->standard->questions) > 0;
    }
}
