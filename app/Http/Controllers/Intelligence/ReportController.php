<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssessmentReportExport;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Report Controller
 * 
 * Handles report generation and export (PDF/Excel)
 */
class ReportController extends Controller
{
    /**
     * Export assessment report to PDF
     *
     * @param int $sessionId
     * @return Response
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
     * Export assessment data to Excel
     *
     * @param int $sessionId
     * @return BinaryFileResponse
     */
    public function exportExcel(int $sessionId): BinaryFileResponse
    {
        $userId = auth()->id();
        $session = AssessmentSession::where('user_id', $userId)->findOrFail($sessionId);
        return Excel::download(
            new AssessmentReportExport($sessionId), 
            "ISO27001:2022_Audit_Data_{$session->id}.xlsx"
        );
    }
}
