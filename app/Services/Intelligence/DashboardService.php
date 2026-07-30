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
        // 1. Get all sessions (owned + invited) for the portfolio view
        $allSessions = AssessmentSession::where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
            })
            ->orderByDesc('updated_at')
            ->get();
            
        $completedCycles = $allSessions->where('status', 'completed')->count();

        // 2. Get the "Latest State" of ALL assessable controls across sessions
        $rawResults = \App\Models\AssessmentResult::with(['standard', 'session'])
            ->whereHas('session', function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
            })
            ->where('status', 'completed')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('iso_standard_id')
            ->values();

        $results = $this->filterAssessableResults($rawResults);

        if ($results->isEmpty()) {
            return ['allSessions' => collect(), 'hasData' => false];
        }

        // 3. Calculate Global Stats (matching Admin Dashboard & Reports methodology)
        $stats = $this->calculateResultStats($results);
        
        $completedSessionsWithScore = $allSessions->where('status', 'completed')->where('overall_maturity_score', '>', 0);
        if ($completedSessionsWithScore->count() > 0) {
            $averageMaturity = (float) $completedSessionsWithScore->avg('overall_maturity_score');
        } else {
            $sessionsWithScore = $allSessions->where('overall_maturity_score', '>', 0);
            $averageMaturity = $sessionsWithScore->count() > 0 ? (float) $sessionsWithScore->avg('overall_maturity_score') : 0;
        }
        
        $complianceScore = $this->calculateCompliancePercentage($averageMaturity);
        $statusKematangan = match (true) {
            $averageMaturity >= 4.5 => 'Optimized (Level 5)',
            $averageMaturity >= 3.5 => 'Managed (Level 4)',
            $averageMaturity >= 2.5 => 'Defined (Level 3)',
            $averageMaturity >= 1.5 => 'Limited/Repeatable (Level 2)',
            $averageMaturity >= 0.5 => 'Initial (Level 1)',
            default                 => 'Non-existent (Level 0)',
        };

        // Delta is not applicable for a global view unless comparing timeframes
        $complianceDelta = 0;

        // 4. Global Findings and Active Tasks (Average gap counts across all COMPLETED audit sessions)
        $findings = $this->getFindings($results, 4);
        $highestGaps = $findings->sortBy('maturity_rating')->take(5);

        $completedSessionsList = $allSessions->where('status', 'completed');
        $distribution = ['compliant' => 0, 'partial' => 0, 'non_compliant' => 0, 'unassessed' => 0];
        $radarData = ['labels' => [], 'data' => []];

        $checkFns = [
            'Clauses (4-10)' => fn($code) => str_starts_with($code, '4.') || str_starts_with($code, '5.') || str_starts_with($code, '6.') || str_starts_with($code, '7.') || str_starts_with($code, '8.') || str_starts_with($code, '9.') || str_starts_with($code, '10.'),
            'A.5 Org Controls' => fn($code) => str_starts_with($code, 'A.5'),
            'A.6 People Controls' => fn($code) => str_starts_with($code, 'A.6'),
            'A.7 Physical Controls' => fn($code) => str_starts_with($code, 'A.7'),
            'A.8 Tech Controls' => fn($code) => str_starts_with($code, 'A.8'),
        ];

        if ($completedSessionsList->count() > 0) {
            $sessionGapCounts = [];
            $sessionHighRiskCounts = [];
            $sessionMedRiskCounts = [];
            $sessionLowRiskCounts = [];

            $distCompliantArr = [];
            $distPartialArr = [];
            $distNonCompliantArr = [];
            $distUnassessedArr = [];

            $domainAveragesList = [
                'Clauses (4-10)' => [],
                'A.5 Org Controls' => [],
                'A.6 People Controls' => [],
                'A.7 Physical Controls' => [],
                'A.8 Tech Controls' => [],
            ];

            foreach ($completedSessionsList as $cSession) {
                $cResults = \App\Models\AssessmentResult::with('standard')
                    ->where('session_id', $cSession->id)
                    ->get()
                    ->filter(fn($r) => $r->standard && is_array($r->standard->questions) && count($r->standard->questions) > 0);

                $cApplicable = $cResults->where('is_applicable', true);
                $cGaps = $cApplicable->whereNotNull('maturity_rating')->where('maturity_rating', '<', 5);

                $sessionGapCounts[] = $cGaps->count();
                $sessionHighRiskCounts[] = $cApplicable->filter(fn($r) => $r->calculated_risk_priority === 'High' || ($r->risk_priority === null && $r->maturity_rating <= 2))->count();
                $sessionMedRiskCounts[] = $cApplicable->filter(fn($r) => $r->calculated_risk_priority === 'Medium' || ($r->risk_priority === null && $r->maturity_rating == 3))->count();
                $sessionLowRiskCounts[] = $cApplicable->filter(fn($r) => $r->calculated_risk_priority === 'Low' || ($r->risk_priority === null && $r->maturity_rating == 4))->count();

                $distCompliantArr[] = $cApplicable->where('maturity_rating', '>=', 4)->count();
                $distPartialArr[] = $cApplicable->whereBetween('maturity_rating', [2, 3])->count();
                $distNonCompliantArr[] = $cApplicable->where('maturity_rating', '<', 2)->count();
                $distUnassessedArr[] = $cResults->where('is_applicable', false)->count();

                foreach ($checkFns as $dName => $fn) {
                    $group = $cApplicable->filter(fn($r) => $fn($r->standard->code ?? ''));
                    $avgScore = $group->count() > 0 ? $group->avg('maturity_rating') : 0;
                    $domainAveragesList[$dName][] = $avgScore;
                }
            }

            $totalGaps = (int) round(array_sum($sessionGapCounts) / count($sessionGapCounts));
            $highRiskGapsCount = (int) round(array_sum($sessionHighRiskCounts) / count($sessionHighRiskCounts));
            $mediumRiskGapsCount = (int) round(array_sum($sessionMedRiskCounts) / count($sessionMedRiskCounts));
            $lowRiskGapsCount = (int) round(array_sum($sessionLowRiskCounts) / count($sessionLowRiskCounts));

            $distribution = [
                'compliant'     => (int) round(array_sum($distCompliantArr) / count($distCompliantArr)),
                'partial'       => (int) round(array_sum($distPartialArr) / count($distPartialArr)),
                'non_compliant' => (int) round(array_sum($distNonCompliantArr) / count($distNonCompliantArr)),
                'unassessed'    => (int) round(array_sum($distUnassessedArr) / count($distUnassessedArr)),
            ];

            foreach ($domainAveragesList as $dName => $scores) {
                $radarData['labels'][] = $dName;
                $radarData['data'][] = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
            }
        } else {
            $totalGaps = $results->where('is_applicable', true)->whereNotNull('maturity_rating')->where('maturity_rating', '<', 5)->count();
            $highRiskGapsCount = $results->where('is_applicable', true)->filter(fn($r) => $r->calculated_risk_priority === 'High' || ($r->risk_priority === null && $r->maturity_rating <= 2))->count();
            $mediumRiskGapsCount = $results->where('is_applicable', true)->filter(fn($r) => $r->calculated_risk_priority === 'Medium' || ($r->risk_priority === null && $r->maturity_rating == 3))->count();
            $lowRiskGapsCount = $results->where('is_applicable', true)->filter(fn($r) => $r->calculated_risk_priority === 'Low' || ($r->risk_priority === null && $r->maturity_rating == 4))->count();

            $distribution = $this->calculateComplianceBreakdown($results);

            foreach ($checkFns as $domainName => $checkFn) {
                $domainResults = $results->filter(fn($r) => $checkFn($r->standard->code ?? ''));
                $avg = $domainResults->count() > 0 ? $domainResults->avg('maturity_rating') : 0;
                $radarData['labels'][] = $domainName;
                $radarData['data'][] = round($avg, 1);
            }
        }

        // Active CAPA Tasks: Get top 5 pending tasks across all sessions of the user (not collapsed by unique standard)
        $activeTasks = \App\Models\AssessmentResult::with(['standard', 'session'])
            ->whereHas('session', function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
            })
            ->where('status', 'completed')
            ->where('is_applicable', true)
            ->where('maturity_rating', '<', 4)
            ->where('treatment_status', '!=', 'closed')
            ->whereNotNull('treatment_due_date')
            ->orderBy('treatment_due_date')
            ->take(5)
            ->get();

        // 5. Variables for Blade (Aligned with Admin Reports & Standard Risk Classification)
        $totalCount = $stats['total'];
        $answeredCount = $stats['answered'];
        $assessmentProgress = $stats['completion_percentage'];
        $criticalGapCount = $highRiskGapsCount;
        $highGapCount = $mediumRiskGapsCount;
        $distTotal = max(1, ($distribution['compliant'] ?? 0) + ($distribution['partial'] ?? 0) + ($distribution['non_compliant'] ?? 0) + ($distribution['unassessed'] ?? 0));

        // 6. Active Session Progress (latest session only)
        $latestSession = $allSessions->where('status', 'in_progress')->first()
            ?? $allSessions->first();

        $totalIsoControls = 137;

        $activeSessionAnswered = 0;
        $activeSessionProgress = 0;
        if ($latestSession) {
            if ($latestSession->status === 'completed') {
                $activeSessionAnswered = 137;
            } else {
                $activeSessionAnswered = \App\Models\AssessmentResult::where('session_id', $latestSession->id)
                    ->where(function($q) {
                        $q->where('is_applicable', false)
                          ->orWhere('status', 'completed')
                          ->orWhereNotNull('maturity_rating');
                    })
                    ->count();
            }
            $activeSessionProgress = $totalIsoControls > 0
                ? min(100, round(($activeSessionAnswered / $totalIsoControls) * 100))
                : 0;
        }

        // 7. Historical Coverage (unique controls ever completed across ALL sessions)
        $historicalCoveredCount = \App\Models\AssessmentResult::with('standard')
            ->whereHas('session', function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
            })
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

        // Risk Priority calculation
        $riskPriority = 'Low';
        $riskBadge = 'bg-emerald-50 text-emerald-600 border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white';
        if ($criticalGapCount > 0) {
            $riskPriority = 'High';
            $riskBadge = 'bg-rose-50 text-rose-600 border-rose-100 group-hover:bg-rose-500 group-hover:text-white';
        } elseif ($totalGaps > 0) {
            $riskPriority = 'Medium';
            $riskBadge = 'bg-amber-50 text-amber-600 border-amber-100 group-hover:bg-amber-500 group-hover:text-white';
        }

        // 9. Generate Algorithmic Executive Summary (Global Overview)
        $summaryParts = [];
        
        $summaryParts[] = "The organization's overall ISO 27001:2022 compliance posture across completed audit sessions currently stands at <strong class='text-blue-200'>{$complianceScore}%</strong> (Overall Maturity: {$statusKematangan}).";
        
        if (count($trendData['data']) >= 2) {
            $firstScore = $trendData['data'][0];
            $lastScore = end($trendData['data']);
            if ($lastScore > $firstScore) {
                $summaryParts[] = "Across completed audit cycles, there is an overall positive trend, improving from {$firstScore}% in the initial assessment.";
            } elseif ($lastScore < $firstScore) {
                $summaryParts[] = "Across completed audit cycles, there has been a decline in compliance compared to the initial score of {$firstScore}%.";
            } else {
                $summaryParts[] = "Global compliance levels have remained stable across completed assessment cycles.";
            }
        }
        
        $riskBadgeClass = match($riskPriority) {
            'High' => 'text-rose-200',
            'Medium' => 'text-amber-200',
            default => 'text-emerald-200',
        };
        $summaryParts[] = "The overall risk profile is currently evaluated at <strong class='{$riskBadgeClass}'>{$riskPriority} Risk Level</strong>.";

        if ($highestGaps->count() > 0) {
            $topGap = $highestGaps->first();
            $summaryParts[] = "Immediate priority should be directed towards <strong class='text-white'>{$topGap->standard->code}</strong>.";
        } else {
            $summaryParts[] = "No high-priority gap remediation is currently pending.";
        }
        
        if ($complianceScore >= 80) {
            $summaryParts[] = "The overall Information Security Management System (ISMS) is functioning effectively and is well-prepared for external certification.";
        } elseif ($complianceScore >= 50) {
            $summaryParts[] = "The overall ISMS is developing well, but requires targeted remediation efforts to close remaining active tasks.";
        } else {
            $summaryParts[] = "Significant foundational work is still required across multiple domains to achieve an acceptable global security baseline.";
        }
        
        $executiveSummary = implode(' ', $summaryParts);

        // 10. Executive Summary generated above

        // 11. Recent Audit Trails
        $recentAuditTrails = \App\Models\AuditTrail::with(['user', 'model' => function ($morphTo) {
            $morphTo->morphWith([\App\Models\AssessmentResult::class => ['standard']]);
        }])
        ->where('user_id', $userId)
        ->where('model_type', \App\Models\AssessmentResult::class)
        ->orderByDesc('created_at')
        ->take(4)
        ->get();

        // 13. Assessor Badge
        $assessorBadge = match(true) {
            $completedCycles >= 3 => ['title' => 'Expert Assessor', 'icon' => 'fa-medal', 'color' => 'text-amber-500 bg-amber-50 border-amber-200'],
            $completedCycles >= 1 => ['title' => 'ISO Practitioner', 'icon' => 'fa-shield-halved', 'color' => 'text-blue-600 bg-blue-50 border-blue-200'],
            default => ['title' => 'Novice Assessor', 'icon' => 'fa-seedling', 'color' => 'text-emerald-600 bg-emerald-50 border-emerald-200']
        };

        // Risk Priority calculated above

        // AI Recommendation Status calculation
        $aiRecStatus = 'Pending';
        $aiRecBadge = 'bg-slate-50 text-slate-600 border-slate-100 group-hover:bg-slate-600 group-hover:text-white';
        if ($latestSession) {
            $cacheStatus = \Illuminate\Support\Facades\Cache::get("session_{$latestSession->id}_summary_status");
            if ($cacheStatus === 'generating' || $cacheStatus === 'processing') {
                $aiRecStatus = 'Generating';
                $aiRecBadge = 'bg-amber-50 text-amber-600 border-amber-100 group-hover:bg-amber-600 group-hover:text-white animate-pulse';
            } elseif ($latestSession->ai_summary) {
                $aiRecStatus = 'Completed';
                $aiRecBadge = 'bg-emerald-50 text-emerald-600 border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white';
            }
        }

        $hasData = true;

        return compact(
            'hasData', 'latestSession', 'allSessions', 'complianceScore', 'complianceDelta',
            'averageMaturity', 'statusKematangan', 'stats', 'highestGaps', 'totalGaps',
            'completedCycles', 'distribution', 'activeTasks',
            'totalCount', 'answeredCount', 'assessmentProgress', 'criticalGapCount', 'highGapCount',
            'highRiskGapsCount', 'mediumRiskGapsCount', 'lowRiskGapsCount', 'distTotal',
            'totalIsoControls', 'activeSessionAnswered', 'activeSessionProgress',
            'historicalCoveredCount', 'historicalCoveragePercent', 'trendData', 'executiveSummary',
            'radarData', 'recentAuditTrails', 'assessorBadge',
            'riskPriority', 'riskBadge', 'aiRecStatus', 'aiRecBadge'
        );
    }

}
