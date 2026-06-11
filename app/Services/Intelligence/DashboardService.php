<?php

namespace App\Services\Intelligence;

use App\Models\AssessmentSession;
use App\Services\Traits\MaturityHelper;
use App\Services\Traits\SessionLoader;
use App\Services\Traits\ResultCalculator;

class DashboardService
{
    use MaturityHelper, SessionLoader, ResultCalculator;

    /**
     * Get dashboard data.
     * Queries are lightweight with eager loading.
     */
    public function getDashboardData(int $userId, ?int $selectedSessionId = null): array
    {
        return $this->buildDashboardData($userId, $selectedSessionId);
    }

    /**
     * Build dashboard data
     */
    private function buildDashboardData(int $userId, ?int $selectedSessionId = null): array
    {
        // 1. Get all sessions for the portfolio view
        $allSessions = AssessmentSession::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get();
            
        $completedCycles = $allSessions->where('status', 'completed')->count();

        // 2. Get the "Latest State" of ALL controls across ALL sessions
        // By ordering by updated_at desc and using unique('standard_id'), 
        // we get the most recent score for each ISO 27001 control.
        $results = \App\Models\AssessmentResult::with(['standard', 'session'])
            ->whereHas('session', fn($q) => $q->where('user_id', $userId))
            ->where('status', 'completed')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('iso_standard_id')
            ->values();

        if ($results->isEmpty()) {
            return ['allSessions' => collect(), 'hasData' => false];
        }

        // 3. Calculate Global Stats
        $stats = $this->calculateResultStats($results);
        
        $completedResults = $results->where('status', 'completed');
        $averageMaturity = $completedResults->count() > 0 ? $completedResults->avg('maturity_rating') : 0;
        
        $complianceScore = $this->calculateCompliancePercentage($averageMaturity);
        $statusKematangan = $this->getMaturityLabel($averageMaturity);

        // Delta is not applicable for a global view unless comparing timeframes
        $complianceDelta = 0;

        // 4. Global Findings and Active Tasks
        $findings = $this->getFindings($results);
        $totalGaps = $findings->count();
        $highestGaps = $findings->sortBy('maturity_rating')->take(5);
        $activeTasks = $this->getResultsWithPendingTreatment($results)->take(10);
        $distribution = $this->calculateComplianceBreakdown($results);

        // 5. Variables for Blade
        $totalCount = $stats['total'];
        $answeredCount = $stats['answered'];
        $assessmentProgress = $stats['completion_percentage'];
        $criticalGapCount = $results->where('maturity_rating', 1)->count();
        $highGapCount = $results->where('maturity_rating', 2)->count();
        $distTotal = max(1, ($distribution['compliant'] ?? 0) + ($distribution['partial'] ?? 0) + ($distribution['non_compliant'] ?? 0) + ($distribution['unassessed'] ?? 0));

        // 6. Active Session Progress (latest session only)
        $latestSession = $allSessions->first();
        // Only count standards that have questions (assessable controls)
        $totalIsoControls = \App\Models\IsoStandard::whereNotNull('questions')
            ->where('questions', '!=', '[]')
            ->where('questions', '!=', 'null')
            ->count();

        $activeSessionAnswered = 0;
        $activeSessionProgress = 0;
        if ($latestSession) {
            $activeSessionAnswered = \App\Models\AssessmentResult::where('session_id', $latestSession->id)
                ->where('status', 'completed')
                ->whereHas('standard', function ($q) {
                    $q->whereNotNull('questions')
                      ->where('questions', '!=', '[]')
                      ->where('questions', '!=', 'null');
                })
                ->count();
            $activeSessionProgress = $totalIsoControls > 0
                ? min(100, round(($activeSessionAnswered / $totalIsoControls) * 100))
                : 0;
        }

        // 7. Historical Coverage (unique controls ever completed across ALL sessions)
        $historicalCoveredCount = \App\Models\AssessmentResult::with('standard')
            ->whereHas('session', fn($q) => $q->where('user_id', $userId))
            ->where('status', 'completed')
            ->get()
            ->unique('iso_standard_id')
            ->count();
        $historicalCoveragePercent = $totalIsoControls > 0
            ? round(($historicalCoveredCount / $totalIsoControls) * 100)
            : 0;

        // 8. Compliance Trend Data (chronological, COMPLETED sessions only)
        $trendData = [
            'labels' => [],
            'data'   => []
        ];

        $completedSessions = $allSessions
            ->where('status', 'completed')
            ->sortBy('created_at')
            ->values();

        foreach ($completedSessions as $session) {
            // Calculate compliance from actual results for each completed session
            $sessionResults = \App\Models\AssessmentResult::where('session_id', $session->id)
                ->where('status', 'completed')
                ->get();

            if ($sessionResults->isEmpty()) continue;

            $sessionAvgMaturity = $sessionResults->avg('maturity_rating');
            $sessionCompliance  = $this->calculateCompliancePercentage($sessionAvgMaturity);

            $trendData['labels'][] = $session->name;
            $trendData['data'][]   = $sessionCompliance;
        }

        // 9. Generate Algorithmic Executive Summary
        $summaryParts = [];
        
        $summaryParts[] = "The organization's global ISO 27001:2022 compliance posture currently stands at <strong class='text-blue-200'>{$complianceScore}%</strong> (Maturity: {$statusKematangan}).";
        
        if (count($trendData['data']) >= 2) {
            $firstScore = $trendData['data'][0];
            $lastScore = end($trendData['data']);
            if ($lastScore > $firstScore) {
                $summaryParts[] = "Historically, there is a positive trend, improving from {$firstScore}% in the initial assessment.";
            } elseif ($lastScore < $firstScore) {
                $summaryParts[] = "Historically, there has been a decline in compliance compared to the initial score of {$firstScore}%.";
            } else {
                $summaryParts[] = "Compliance levels have remained stable across assessment cycles.";
            }
        }
        
        if ($highestGaps->count() > 0) {
            $topGap = $highestGaps->first();
            $summaryParts[] = "Currently, there are <strong class='text-orange-200'>{$totalGaps}</strong> identified gaps requiring remediation.";
            $summaryParts[] = "Immediate attention should be directed towards <strong class='text-white'>{$topGap->standard->code}</strong>, which is currently a priority risk.";
        } else {
            $summaryParts[] = "Excellent progress: No critical or high-priority gaps are currently identified.";
        }
        
        if ($complianceScore >= 80) {
            $summaryParts[] = "The Information Security Management System (ISMS) is functioning effectively and is well-prepared for external certification.";
        } elseif ($complianceScore >= 50) {
            $summaryParts[] = "The ISMS is developing well, but requires targeted efforts to close remaining active tasks.";
        } else {
            $summaryParts[] = "Significant foundational work is still required across multiple clauses to achieve an acceptable security baseline.";
        }
        
        $executiveSummary = implode(' ', $summaryParts);

        $hasData = true;

        return compact(
            'hasData', 'latestSession', 'allSessions', 'complianceScore', 'complianceDelta',
            'averageMaturity', 'statusKematangan', 'stats', 'highestGaps', 'totalGaps',
            'completedCycles', 'distribution', 'activeTasks',
            'totalCount', 'answeredCount', 'assessmentProgress', 'criticalGapCount', 'highGapCount', 'distTotal',
            'totalIsoControls', 'activeSessionAnswered', 'activeSessionProgress',
            'historicalCoveredCount', 'historicalCoveragePercent', 'trendData', 'executiveSummary'
        );
    }

}
