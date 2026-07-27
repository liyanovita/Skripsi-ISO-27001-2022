<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;
use App\Models\User;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getReportData($request);
        return view('admin.reports.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $data['date'] = now()->format('d F Y H:i');

        $pdf = Pdf::loadView('admin.reports.pdf_template', $data);
        return $pdf->download("ISO27001_Compliance_Report_" . date('Y-m-d') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        $hasCompletedSessions = AssessmentSession::where('status', 'completed')->exists();

        $query = AssessmentResult::with(['session.user.organization', 'standard'])
            ->where('is_applicable', true)
            ->whereHas('session', function($q) {
                $q->where('status', 'completed');
            });

        $results = $query->orderBy('session_id')->get();

        $headers = [
            'Session Name', 'User Name', 'User Email', 'Business Sector', 'Organization Scale',
            'ISO Code', 'ISO Title', 'Type', 'Maturity Rating', 'Gap', 'Risk Priority', 'Is Applicable',
            'Implementation Status', 'CAPA Status', 'CAPA PIC', 'CAPA Due Date', 'Audit Date'
        ];

        $rows = [];
        foreach ($results as $row) {
            $rows[] = [
                $row->session->name ?? 'N/A',
                $row->session->user->name ?? 'N/A',
                $row->session->user->email ?? 'N/A',
                $row->session->user->organization?->business_sector ?: 'N/A',
                $row->session->user->organization?->organization_scale ?: 'N/A',
                $row->standard->code ?? 'N/A',
                $row->standard->title ?? 'N/A',
                $row->standard->type ?? 'N/A',
                $row->maturity_rating,
                $row->gap,
                $row->calculated_risk_priority ?: 'N/A',
                $row->is_applicable ? 'Yes' : 'No',
                $row->implementation_status ?: 'N/A',
                $row->treatment_status ?: 'N/A',
                $row->treatment_pic ?: 'N/A',
                $row->treatment_due_date ? $row->treatment_due_date->format('Y-m-d') : 'N/A',
                $row->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        $filename = 'iso27001_compliance_report_' . date('Y-m-d') . '.xlsx';
        return ExcelExportService::download($filename, $headers, $rows, 'Compliance Report');
    }

    private function getReportData(Request $request)
    {
        $sessions = AssessmentSession::with('user')->where('status', 'completed')->orderBy('name', 'asc')->get();

        $selectedSessionId = $request->filled('session_id') ? (int) $request->session_id : null;
        $selectedSession = $selectedSessionId ? AssessmentSession::with('user.organization')->find($selectedSessionId) : null;

        // 1. Core Summary Metrics
        $totalSessions = AssessmentSession::count();
        $completedSessions = AssessmentSession::where('status', 'completed')->count();

        $sessionQuery = AssessmentSession::where('status', 'completed');
        if ($selectedSessionId) {
            $sessionQuery->where('id', $selectedSessionId);
        }

        $averageScore = (clone $sessionQuery)->where('overall_maturity_score', '>', 0)
            ->avg('overall_maturity_score') ?? 0;

        $overallCompliancePercentage = round(($averageScore / 5) * 100, 1);

        // Base query for assessment results
        $resultBase = AssessmentResult::join('iso_standards', 'assessment_results.iso_standard_id', '=', 'iso_standards.id')
            ->join('assessment_sessions', 'assessment_results.session_id', '=', 'assessment_sessions.id')
            ->whereNull('assessment_sessions.deleted_at')
            ->where('assessment_results.is_applicable', true)
            ->where('assessment_results.maturity_rating', '>=', 0)
            ->where('assessment_sessions.status', 'completed');

        if ($selectedSessionId) {
            $resultBase->where('assessment_results.session_id', $selectedSessionId);
        }

        // Compliance Breakdown (Compliant, Needs Improvement, Non-Compliant)
        $compliantCount = (clone $resultBase)->where('assessment_results.maturity_rating', '>=', 4)->count();
        $needsImprovementCount = (clone $resultBase)->where('assessment_results.maturity_rating', '>=', 2)->where('assessment_results.maturity_rating', '<', 4)->count();
        $nonCompliantCount = (clone $resultBase)->where('assessment_results.maturity_rating', '<', 2)->count();

        // Risk & Gap Counts (Total Gaps = controls below target Level 5)
        $totalGaps = (clone $resultBase)->where('assessment_results.maturity_rating', '<', 5)->count();
        $highRiskCount = (clone $resultBase)->where(function($q) {
            $q->where('assessment_results.risk_priority', 'High')
              ->orWhere(function($q2) {
                  $q2->whereNull('assessment_results.risk_priority')
                     ->where('assessment_results.maturity_rating', '<=', 2);
              });
        })->count();
        $mediumRiskCount = (clone $resultBase)->where(function($q) {
            $q->where('assessment_results.risk_priority', 'Medium')
              ->orWhere(function($q2) {
                  $q2->whereNull('assessment_results.risk_priority')
                     ->where('assessment_results.maturity_rating', 3);
              });
        })->count();
        $lowRiskCount = (clone $resultBase)->where(function($q) {
            $q->where('assessment_results.risk_priority', 'Low')
              ->orWhere(function($q2) {
                  $q2->whereNull('assessment_results.risk_priority')
                     ->where('assessment_results.maturity_rating', 4);
              });
        })->count();

        // 2. Average Maturity Score by Business Sector (excluding soft-deleted sessions)
        $sectorPerformance = DB::table('organizations')
            ->join('users', 'organizations.id', '=', 'users.organization_id')
            ->join('assessment_sessions', 'users.id', '=', 'assessment_sessions.user_id')
            ->whereNull('assessment_sessions.deleted_at')
            ->whereNotNull('organizations.business_sector')
            ->where('organizations.business_sector', '!=', '')
            ->where('assessment_sessions.status', 'completed')
            ->select('organizations.business_sector', DB::raw('AVG(assessment_sessions.overall_maturity_score) as avg_score'), DB::raw('COUNT(assessment_sessions.id) as sessions_count'))
            ->groupBy('organizations.business_sector')
            ->orderByDesc('avg_score')
            ->get();

        // 3. Top 5 failing controls with Gap & Risk Level
        $failingControls = (clone $resultBase)
            ->where('assessment_results.maturity_rating', '<', 4)
            ->select(
                'iso_standards.code', 
                'iso_standards.title', 
                'iso_standards.type', 
                DB::raw('AVG(assessment_results.maturity_rating) as avg_rating'), 
                DB::raw('ROUND(AVG(4.0 - assessment_results.maturity_rating), 1) as avg_gap'),
                DB::raw("CASE WHEN AVG(assessment_results.maturity_rating) < 2 THEN 'High' WHEN AVG(assessment_results.maturity_rating) < 3 THEN 'Medium' ELSE 'Low' END as calculated_risk"),
                DB::raw('COUNT(assessment_results.id) as occurrences')
            )
            ->groupBy('iso_standards.id', 'iso_standards.code', 'iso_standards.title', 'iso_standards.type')
            ->orderBy('avg_rating', 'asc')
            ->take(5)
            ->get();

        // 4. ISO 27001:2022 Performance per Security & Management Domain (Clauses 4-10 & Annex A.5-A.8 Combined)
        $mainClauses = IsoStandard::with('children.children.children')
            ->whereNull('parent_id')
            ->whereIn('type', ['clause', 'clausa'])
            ->orderByRaw('LENGTH(code) ASC, code ASC')
            ->get();

        $domainStats = [];
        $clauseStats = [];
        foreach ($mainClauses as $clause) {
            $childIds = $this->getRecursiveChildIds($clause);
            
            $avgClauseRating = (clone $resultBase)
                ->whereIn('assessment_results.iso_standard_id', $childIds)
                ->avg('assessment_results.maturity_rating') ?? 0;

            $item = [
                'code' => "Clause {$clause->code}",
                'title' => "Clause {$clause->code} ({$clause->title})",
                'avg_rating' => round($avgClauseRating, 2),
            ];
            $domainStats[] = $item;
            $clauseStats[] = $item;
        }

        // Annex A Controls Domains Breakdown (A.5, A.6, A.7, A.8)
        $annexDomains = [
            'A.5' => 'Organizational Controls',
            'A.6' => 'People Controls',
            'A.7' => 'Physical Controls',
            'A.8' => 'Technological Controls',
        ];

        $annexStats = [];
        foreach ($annexDomains as $codePrefix => $title) {
            $avgAnnexRating = (clone $resultBase)
                ->where('iso_standards.code', 'like', "{$codePrefix}%")
                ->avg('assessment_results.maturity_rating') ?? 0;

            $item = [
                'code' => $codePrefix,
                'title' => "{$codePrefix} {$title}",
                'avg_rating' => round($avgAnnexRating, 2),
            ];
            $domainStats[] = $item;
            $annexStats[] = $item;
        }

        // Executive Synthesis Summary Calculation
        $sortedDomains = collect($domainStats)->sortBy('avg_rating');
        $weakestDomain = $sortedDomains->first();

        $sortedClauses = collect($clauseStats)->sortBy('avg_rating');
        $weakestClause = $sortedClauses->first();

        $sortedAnnex = collect($annexStats)->sortBy('avg_rating');
        $weakestAnnex = $sortedAnnex->first();

        $matClassification = AssessmentSession::getMaturityLevelClassification((float)$averageScore);

        $executiveSummary = [
            'level_name' => $matClassification['name'],
            'level_number' => $matClassification['level'],
            'overall_compliance_percentage' => $overallCompliancePercentage,
            'average_score' => number_format($averageScore, 2),
            'total_gaps' => $totalGaps,
            'high_risk_count' => $highRiskCount,
            'weakest_domain' => $weakestDomain ? $weakestDomain['title'] : 'N/A',
            'weakest_domain_rating' => $weakestDomain ? number_format($weakestDomain['avg_rating'], 2) : '0.00',
            'weakest_clause' => $weakestClause ? $weakestClause['title'] : 'N/A',
            'weakest_clause_rating' => $weakestClause ? number_format($weakestClause['avg_rating'], 2) : '0.00',
            'weakest_annex' => $weakestAnnex ? $weakestAnnex['title'] : 'N/A',
            'weakest_annex_rating' => $weakestAnnex ? number_format($weakestAnnex['avg_rating'], 2) : '0.00',
        ];

        $selectedSessionId = null;
        $selectedSession = null;

        return compact(
            'totalSessions',
            'completedSessions',
            'averageScore',
            'overallCompliancePercentage',
            'compliantCount',
            'needsImprovementCount',
            'nonCompliantCount',
            'totalGaps',
            'highRiskCount',
            'mediumRiskCount',
            'lowRiskCount',
            'sectorPerformance',
            'failingControls',
            'domainStats',
            'clauseStats',
            'annexStats',
            'executiveSummary',
            'sessions',
            'selectedSessionId',
            'selectedSession'
        );
    }

    private function getRecursiveChildIds($standard)
    {
        $ids = [];
        foreach ($standard->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getRecursiveChildIds($child));
        }
        return $ids;
    }
}
