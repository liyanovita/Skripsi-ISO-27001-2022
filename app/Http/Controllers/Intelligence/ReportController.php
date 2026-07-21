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
     */
    public function exportPdf(int $sessionId)
    {
        $userId = auth()->id();
        $user = auth()->user();
        
        $query = AssessmentSession::with(['results.standard']);
        if (!$user || !$user->isAdmin()) {
            $query->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
            });
        }
        
        $session = $query->findOrFail($sessionId);

        if ($session->status !== 'completed') {
            return redirect()->back()->with('error', __('This report can only be downloaded after the assessment session status is marked as completed.'));
        }

        $overallMaturity = $session->overall_maturity_score ?? 0.0;
        $complianceScore = (int) round(($overallMaturity / 5) * 100);

        $complianceStatusText = 'Non-Compliant';
        if ($complianceScore >= 80) {
            $complianceStatusText = 'Compliant';
        } elseif ($complianceScore >= 50) {
            $complianceStatusText = 'Partially Compliant';
        }

        $maturityLevelLabel = match(true) {
            $overallMaturity <= 0.5 => 'Non-existent (Level 0)',
            $overallMaturity <= 1.5 => 'Initial (Level 1)',
            $overallMaturity <= 2.5 => 'Limited/Repeatable (Level 2)',
            $overallMaturity <= 3.5 => 'Defined (Level 3)',
            $overallMaturity <= 4.5 => 'Managed (Level 4)',
            default => 'Optimized (Level 5)',
        };

        // Gaps are applicable, completed, and maturity < 4
        $gapResults = $session->results
            ->filter(fn($r) => 
                $r->is_applicable &&
                $r->status === 'completed' &&
                $r->maturity_rating >= 0 &&
                $r->maturity_rating < 4 &&
                $r->standard
            )
            ->sortBy('maturity_rating')
            ->values();

        // Improvement tracking are applicable, completed, and have a priority or action plan or PIC
        $trackingResults = $session->results
            ->filter(fn($r) =>
                $r->is_applicable &&
                $r->status === 'completed' &&
                $r->standard &&
                ($r->maturity_rating < 4 || $r->treatment_pic || $r->treatment_status !== 'open' || $r->treatment_progress > 0)
            )
            ->sortBy('standard.code')
            ->values();

        $data = [
            'session' => $session,
            'results' => $gapResults, // For backward compatibility / gap list
            'allResults' => $session->results,
            'gapResults' => $gapResults,
            'trackingResults' => $trackingResults,
            'complianceScore' => $complianceScore,
            'complianceStatusText' => $complianceStatusText,
            'overallMaturity' => $overallMaturity,
            'maturityLevelLabel' => $maturityLevelLabel,
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
     */
    public function exportExcel(int $sessionId)
    {
        $userId = auth()->id();
        $user = auth()->user();
        
        $query = AssessmentSession::query();
        if (!$user || !$user->isAdmin()) {
            $query->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
            });
        }
        
        $session = $query->findOrFail($sessionId);

        if ($session->status !== 'completed') {
            return redirect()->back()->with('error', __('This report can only be downloaded after the assessment session status is marked as completed.'));
        }

        return Excel::download(
            new AssessmentReportExport($sessionId), 
            "ISO27001:2022_Audit_Data_{$session->id}.xlsx"
        );
    }
}
