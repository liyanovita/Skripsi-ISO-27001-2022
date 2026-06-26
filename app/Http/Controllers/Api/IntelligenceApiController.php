<?php

namespace App\Http\Controllers\Api;

use App\Services\Intelligence\AnalyticsService;
use App\Services\Intelligence\DashboardService;
use App\Services\Intelligence\AiSummaryService;
use App\Models\AssessmentSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssessmentReportExport;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Schema(
 *     schema="DashboardData",
 *     type="object",
 *     title="Dashboard Data",
 *     description="Dashboard analytics and metrics",
 *     @OA\Property(property="total_sessions", type="integer", example=5),
 *     @OA\Property(property="completed_sessions", type="integer", example=3),
 *     @OA\Property(property="avg_maturity_score", type="number", format="float", example=3.25),
 *     @OA\Property(property="compliance_percentage", type="number", format="float", example=75.5),
 *     @OA\Property(
 *         property="recent_sessions",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(property="overall_maturity_score", type="number", format="float")
 *         )
 *     ),
 *     @OA\Property(
 *         property="maturity_distribution",
 *         type="object",
 *         @OA\Property(property="level_0", type="integer", example=5),
 *         @OA\Property(property="level_1", type="integer", example=10),
 *         @OA\Property(property="level_2", type="integer", example=15),
 *         @OA\Property(property="level_3", type="integer", example=20),
 *         @OA\Property(property="level_4", type="integer", example=25),
 *         @OA\Property(property="level_5", type="integer", example=39)
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="TacticalAnalytics",
 *     type="object",
 *     title="Tactical Analytics",
 *     description="Detailed tactical analytics calculated from real assessment results",
 *     @OA\Property(
 *         property="sessions",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(property="overall_maturity_score", type="number", format="float")
 *         )
 *     ),
 *     @OA\Property(property="latestSession", type="object", nullable=true),
 *     @OA\Property(
 *         property="comparison",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="latest_score", type="number", format="float", example=3.25),
 *         @OA\Property(property="previous_score", type="number", format="float", example=2.75),
 *         @OA\Property(property="delta", type="number", format="float", example=0.5),
 *         @OA\Property(
 *             property="domains",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="label", type="string", example="Policies"),
 *                 @OA\Property(property="latest", type="number", format="float", example=3.5),
 *                 @OA\Property(property="previous", type="number", format="float", example=2.5),
 *                 @OA\Property(property="delta", type="number", format="float", example=1.0)
 *             )
 *         )
 *     ),
 *     @OA\Property(property="selectedId", type="integer", nullable=true, example=1),
 *     @OA\Property(
 *         property="findings",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/AssessmentResult")
 *     ),
 *     @OA\Property(
 *         property="stats",
 *         type="object",
 *         @OA\Property(property="total_gaps", type="integer", example=12),
 *         @OA\Property(property="critical", type="integer", example=2),
 *         @OA\Property(property="compliant", type="integer", example=40),
 *         @OA\Property(property="partial", type="integer", example=10),
 *         @OA\Property(property="non_compliant", type="integer", example=2),
 *         @OA\Property(property="needs_improvement", type="integer", example=12),
 *         @OA\Property(property="unassessed", type="integer", example=4),
 *         @OA\Property(property="excluded", type="integer", example=3),
 *         @OA\Property(property="total_controls", type="integer", example=66),
 *         @OA\Property(property="scored", type="integer", example=62),
 *         @OA\Property(property="assessed", type="integer", example=62)
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="StrategicAnalytics",
 *     type="object",
 *     title="Strategic Analytics",
 *     description="High-level strategic analytics calculated from real assessment results",
 *     @OA\Property(
 *         property="sessions",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Q1 2026 Assessment"),
 *             @OA\Property(property="status", type="string", example="completed"),
 *             @OA\Property(property="overall_maturity_score", type="number", format="float", example=3.25)
 *         )
 *     ),
 *     @OA\Property(property="latestSession", type="object", nullable=true),
 *     @OA\Property(
 *         property="comparison",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="latest_score", type="number", format="float", example=3.25),
 *         @OA\Property(property="previous_score", type="number", format="float", example=2.75),
 *         @OA\Property(property="delta", type="number", format="float", example=0.5),
 *         @OA\Property(
 *             property="domains",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="label", type="string", example="Policies"),
 *                 @OA\Property(property="latest", type="number", format="float", example=3.5),
 *                 @OA\Property(property="previous", type="number", format="float", example=2.5),
 *                 @OA\Property(property="delta", type="number", format="float", example=1.0)
 *             )
 *         )
 *     ),
 *     @OA\Property(property="selectedId", type="integer", nullable=true, example=1),
 *     @OA\Property(
 *         property="stats",
 *         type="object",
 *         @OA\Property(property="total_gaps", type="integer", example=12),
 *         @OA\Property(property="critical", type="integer", example=2),
 *         @OA\Property(property="compliant", type="integer", example=40),
 *         @OA\Property(property="partial", type="integer", example=10),
 *         @OA\Property(property="non_compliant", type="integer", example=2),
 *         @OA\Property(property="needs_improvement", type="integer", example=12),
 *         @OA\Property(property="unassessed", type="integer", example=4),
 *         @OA\Property(property="excluded", type="integer", example=3),
 *         @OA\Property(property="total_controls", type="integer", example=66)
 *     ),
 *     @OA\Property(
 *         property="maturityDistribution",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         example={2,4,6,10,30}
 *     ),
 *     @OA\Property(
 *         property="complianceBreakdown",
 *         type="object",
 *         @OA\Property(property="compliant", type="integer", example=40),
 *         @OA\Property(property="partial", type="integer", example=10),
 *         @OA\Property(property="non_compliant", type="integer", example=2),
 *         @OA\Property(property="unassessed", type="integer", example=4),
 *         @OA\Property(property="excluded", type="integer", example=3)
 *     ),
 *     @OA\Property(
 *         property="maturityViews",
 *         type="object",
 *         @OA\Property(
 *             property="global",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *                 @OA\Property(property="label", type="string", example="Policies"),
 *                 @OA\Property(property="fullLabel", type="string", example="Policies"),
 *                 @OA\Property(property="value", type="number", format="float", example=3.5)
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="maturityTrends",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="name", type="string", example="Q1 2026 Assessment"),
 *             @OA\Property(property="overall_maturity_score", type="number", format="float", example=3.25)
 *         )
 *     )
 * )
 */
class IntelligenceApiController extends BaseApiController
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected AnalyticsService $analyticsService,
        protected AiSummaryService $aiSummaryService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/intelligence/dashboard",
     *     operationId="getDashboardData",
     *     tags={"Intelligence & Analytics"},
     *     summary="Get dashboard analytics",
     *     description="Retrieve comprehensive dashboard data including sessions, maturity scores, and compliance metrics",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dashboard data retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/DashboardData")
     *         )
     *     )
     * )
     */
    public function dashboard(): JsonResponse
    {
        $data = $this->dashboardService->getDashboardData(auth()->id());
        return $this->successResponse($data, 'Dashboard data retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/intelligence/analytics/tactical",
     *     operationId="getTacticalAnalytics",
     *     tags={"Intelligence & Analytics"},
     *     summary="Get tactical analytics",
     *     description="Retrieve detailed tactical analytics including control analysis, risk distribution, and compliance gaps",
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
     *             @OA\Property(property="message", type="string", example="Tactical analytics retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/TacticalAnalytics")
     *         )
     *     )
     * )
     */
    public function tactical(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getTacticalData(
            auth()->id(),
            $request->get('session_id')
        );

        return $this->successResponse($data, 'Tactical analytics retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/intelligence/analytics/strategic",
     *     operationId="getStrategicAnalytics",
     *     tags={"Intelligence & Analytics"},
     *     summary="Get strategic analytics",
     *     description="Retrieve strategic analytics calculated from real assessment results, including active-control maturity trends, domain comparison, and compliance breakdown",
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
     *             @OA\Property(property="message", type="string", example="Strategic analytics retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StrategicAnalytics")
     *         )
     *     )
     * )
     */
    public function strategic(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getStrategicData(
            auth()->id(),
            $request->get('session_id')
        );

        return $this->successResponse($data, 'Strategic analytics retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/intelligence/ai-summary/{sessionId}",
     *     operationId="generateAiSummary",
     *     tags={"Intelligence & Analytics"},
     *     summary="Generate AI executive summary",
     *     description="Generate an AI-powered executive summary for an assessment session",
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
     *         description="Summary generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Executive summary successfully synthesized."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="summary", type="string", example="The assessment reveals a moderate maturity level...")
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
    public function generateAiSummary(int $sessionId): JsonResponse
    {
        try {
            $session = $this->aiSummaryService->generate($sessionId);

            return $this->successResponse([
                'summary' => $session->ai_summary,
            ], 'Executive summary successfully synthesized.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate summary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/webhook/n8n/ai-summary",
     *     operationId="receiveAiSummaryWebhook",
     *     tags={"Intelligence & Analytics"},
     *     summary="Receive AI summary from N8N webhook",
     *     description="Webhook endpoint to receive AI-generated executive summary from N8N workflow",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="session_id", type="integer", example=1),
     *             @OA\Property(property="ai_summary", type="string", example="The assessment reveals moderate maturity with key areas for improvement...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI summary saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="AI Summary saved successfully")
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
    public function receiveAiSummaryWebhook(Request $request): JsonResponse
    {
        try {
            $session = $this->aiSummaryService->receiveWebhook($request->all());

            return $this->successResponse(
                $session,
                'AI Summary saved successfully'
            );
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Session not found') {
                return $this->errorResponse('Session not found', 404);
            }
            return $this->errorResponse('Failed to save AI summary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/reports/{sessionId}/pdf",
     *     operationId="exportReportPdf",
     *     tags={"Intelligence & Analytics"},
     *     summary="Export assessment report as PDF",
     *     description="Generate and download a comprehensive PDF report for an assessment session",
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
     *         description="PDF report generated successfully",
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
    public function exportPdf(int $sessionId): Response
    {
        $userId = auth()->id();
        $session = AssessmentSession::with(['results.standard'])
            ->where('user_id', $userId)
            ->findOrFail($sessionId);
        
        $data = [
            'session' => $session,
            'results' => $session->results
                ->filter(fn($r) => 
                    $r->is_applicable &&
                    $r->status === 'completed' &&
                    $r->maturity_rating >= 0 &&
                    $r->maturity_rating < 4 &&
                    is_array($r->standard?->questions) &&
                    count($r->standard->questions) > 0
                )
                ->sortBy('maturity_rating')
                ->values(),
            'summary' => $session->ai_summary ?? 'No executive summary generated.',
            'date'    => now()->format('d F Y')
        ];

        $pdf = Pdf::loadView('pages.reports.pdf_template', $data);
        return $pdf->download("ISO27001:2022_Audit_Report_{$session->id}.pdf");
    }

    /**
     * @OA\Get(
     *     path="/api/reports/{sessionId}/excel",
     *     operationId="exportReportExcel",
     *     tags={"Intelligence & Analytics"},
     *     summary="Export assessment data as Excel",
     *     description="Generate and download a detailed Excel spreadsheet with assessment data",
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
     *         description="Excel report generated successfully",
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
    public function exportExcel(int $sessionId): BinaryFileResponse
    {
        $session = AssessmentSession::findOrFail($sessionId);
        return Excel::download(
            new AssessmentReportExport($sessionId), 
            "ISO27001:2022_Audit_Data_{$session->id}.xlsx"
        );
    }
}
