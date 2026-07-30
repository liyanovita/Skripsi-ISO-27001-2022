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
                $r->maturity_rating < 5 &&
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
                ($r->maturity_rating < 5 || $r->treatment_pic || $r->treatment_status !== 'open' || $r->treatment_progress > 0)
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

    /**
     * Export full Assessment Result report to PDF (Comprehensive Strategic Executive Report)
     *
     * @param int $sessionId
     */
    public function exportResultPdf(int $sessionId)
    {
        $userId = auth()->id();
        $user = auth()->user();
        
        $query = AssessmentSession::with(['results.standard', 'organization', 'user']);
        if (!$user || !$user->isAdmin()) {
            $query->where(function($q) use ($userId, $user) {
                $q->where('user_id', $userId)
                  ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
                if ($user?->organization_id) {
                    $q->orWhere('organization_id', $user->organization_id);
                }
            });
        }
        
        $session = $query->findOrFail($sessionId);

        $overallMaturity = $session->overall_maturity_score ?? 0.0;
        $complianceScore = (int) round(($overallMaturity / 5) * 100);

        $maturityLevelLabel = match(true) {
            $overallMaturity <= 0.5 => 'Non-existent (Level 0)',
            $overallMaturity <= 1.5 => 'Initial (Level 1)',
            $overallMaturity <= 2.5 => 'Limited/Repeatable (Level 2)',
            $overallMaturity <= 3.5 => 'Defined (Level 3)',
            $overallMaturity <= 4.5 => 'Managed (Level 4)',
            default => 'Optimized (Level 5)',
        };

        // All results ordered by natural ISO standard code (Clauses 4-10 first, then Annex A controls A.5.1 - A.8.34)
        $results = $session->results
            ->filter(fn($r) => $r->standard)
            ->sort(function($a, $b) {
                $typeA = in_array($a->standard->type ?? '', ['clause', 'clausa']) ? 0 : 1;
                $typeB = in_array($b->standard->type ?? '', ['clause', 'clausa']) ? 0 : 1;

                if ($typeA !== $typeB) {
                    return $typeA <=> $typeB;
                }

                return strnatcasecmp($a->standard->code ?? '', $b->standard->code ?? '');
            })
            ->values();

        // Compute Domain Summaries
        $domainGroups = [
            'Clauses (4-10)' => $results->filter(fn($r) => in_array($r->standard->type ?? '', ['clause', 'clausa'])),
            'A.5 Organizational Controls' => $results->filter(fn($r) => str_starts_with($r->standard->code ?? '', 'A.5')),
            'A.6 People Controls' => $results->filter(fn($r) => str_starts_with($r->standard->code ?? '', 'A.6')),
            'A.7 Physical Controls' => $results->filter(fn($r) => str_starts_with($r->standard->code ?? '', 'A.7')),
            'A.8 Technological Controls' => $results->filter(fn($r) => str_starts_with($r->standard->code ?? '', 'A.8')),
        ];

        $domainStats = [];
        foreach ($domainGroups as $domainName => $group) {
            // Only count assessable controls that have questions
            $assessable = $group->filter(fn($r) => is_array($r->standard->questions) && count($r->standard->questions) > 0);
            $applicableGroup = $assessable->where('is_applicable', true);
            $count = $applicableGroup->count();
            $scored = $applicableGroup->whereNotNull('maturity_rating');
            $avgMaturity = $scored->count() > 0 ? round($scored->avg('maturity_rating'), 2) : 0;
            $compliantCount = $scored->where('maturity_rating', '>=', 4)->count();
            $gapCount = $scored->where('maturity_rating', '<', 5)->count();

            $domainStats[$domainName] = [
                'total' => $assessable->count(),
                'applicable' => $count,
                'excluded' => $assessable->count() - $count,
                'avg_maturity' => $avgMaturity,
                'compliant_count' => $compliantCount,
                'gap_count' => $gapCount,
            ];
        }

        // Parse AI Summary
        $parsedAiSummary = null;
        if ($session->ai_summary) {
            $parsedAiSummary = \App\Services\Intelligence\AiSummaryService::parseSummary($session->ai_summary);
        }

        $pdf = Pdf::loadView('exports.assessment_result_pdf', [
            'session'            => $session,
            'results'            => $results,
            'domainStats'        => $domainStats,
            'overallMaturity'    => $overallMaturity,
            'complianceScore'    => $complianceScore,
            'maturityLevelLabel' => $maturityLevelLabel,
            'parsedAiSummary'    => $parsedAiSummary,
            'date'               => now()->format('d F Y'),
        ])->setPaper('a4', 'portrait');

        $safeName = (string) \Illuminate\Support\Str::of($session->name)
            ->replaceMatches('/[\\\\\/:*?"<>|]+/', '-')
            ->replaceMatches('/\s+/', '_')
            ->trim('._-')
            ->limit(80, '');

        return $pdf->download('Assessment_Result_' . ($safeName ?: 'session') . '.pdf');
    }
}
