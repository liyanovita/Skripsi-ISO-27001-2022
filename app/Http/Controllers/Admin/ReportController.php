<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Core Summary Metrics
        $totalSessions = AssessmentSession::count();
        $completedSessions = AssessmentSession::where('status', 'completed')->count();
        $averageScore = AssessmentSession::where('overall_maturity_score', '>', 0)
            ->avg('overall_maturity_score') ?? 0;

        // 2. Average Maturity Score by Business Sector
        $sectorPerformance = User::whereNotNull('business_sector')
            ->where('business_sector', '!=', '')
            ->join('assessment_sessions', 'users.id', '=', 'assessment_sessions.user_id')
            ->select('users.business_sector', DB::raw('AVG(assessment_sessions.overall_maturity_score) as avg_score'), DB::raw('COUNT(assessment_sessions.id) as sessions_count'))
            ->groupBy('users.business_sector')
            ->orderByDesc('avg_score')
            ->get();

        // 3. Top 5 failing controls (lowest maturity rating across completed sessions, excluding parents)
        $failingControls = AssessmentResult::join('iso_standards', 'assessment_results.iso_standard_id', '=', 'iso_standards.id')
            ->join('assessment_sessions', 'assessment_results.session_id', '=', 'assessment_sessions.id')
            ->where('assessment_sessions.status', 'completed')
            ->where('assessment_results.maturity_rating', '>', 0)
            ->where('assessment_results.maturity_rating', '<', 4) // Less than compliant
            ->select('iso_standards.code', 'iso_standards.title', 'iso_standards.type', DB::raw('AVG(assessment_results.maturity_rating) as avg_rating'), DB::raw('COUNT(assessment_results.id) as occurrences'))
            ->groupBy('iso_standards.id', 'iso_standards.code', 'iso_standards.title', 'iso_standards.type')
            ->orderBy('avg_rating', 'asc')
            ->take(5)
            ->get();

        // 4. Compliance Rates per main ISO Clause (Clause 4 to 10)
        // Average compliance score per main clause
        $mainClauses = IsoStandard::with('children.children.children')
            ->whereNull('parent_id')
            ->whereIn('type', ['clause', 'clausa'])
            ->orderByRaw('LENGTH(code) ASC, code ASC')
            ->get();

        $clauseStats = [];
        foreach ($mainClauses as $clause) {
            // Get all sub-clause requirements IDs (children and grandchildren)
            $childIds = $this->getRecursiveChildIds($clause);
            
            // Calculate average maturity score for this clause
            $avgClauseRating = AssessmentResult::whereIn('iso_standard_id', $childIds)
                ->where('maturity_rating', '>', 0)
                ->avg('maturity_rating') ?? 0;

            $clauseStats[] = [
                'code' => $clause->code,
                'title' => $clause->title,
                'avg_rating' => $avgClauseRating,
            ];
        }

        return view('admin.reports.index', compact(
            'totalSessions',
            'completedSessions',
            'averageScore',
            'sectorPerformance',
            'failingControls',
            'clauseStats'
        ));
    }

    public function exportCsv()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=iso27001_compliance_report_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Fetch all assessment results
        $results = AssessmentResult::with(['session.user', 'standard'])
            ->orderBy('session_id')
            ->get();

        $callback = function() use($results) {
            $file = fopen('php://output', 'w');
            
            // Write CSV headers
            fputcsv($file, [
                'Session Name', 
                'User Name', 
                'User Email', 
                'Business Sector', 
                'Organization Scale',
                'ISO Code', 
                'ISO Title', 
                'Type',
                'Maturity Rating', 
                'Is Applicable',
                'Implementation Status',
                'Risk Priority',
                'CAPA Status',
                'CAPA PIC',
                'CAPA Due Date',
                'Audit Date'
            ]);

            // Write data rows
            foreach ($results as $row) {
                fputcsv($file, [
                    $row->session->name,
                    $row->session->user->name,
                    $row->session->user->email,
                    $row->session->user->business_sector ?: 'N/A',
                    $row->session->user->organization_scale ?: 'N/A',
                    $row->standard->code,
                    $row->standard->title,
                    $row->standard->type,
                    $row->maturity_rating,
                    $row->is_applicable ? 'Yes' : 'No',
                    $row->implementation_status ?: 'N/A',
                    $row->risk_priority ?: 'N/A',
                    $row->treatment_status ?: 'N/A',
                    $row->treatment_pic ?: 'N/A',
                    $row->treatment_due_date ? $row->treatment_due_date->format('Y-m-d') : 'N/A',
                    $row->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
