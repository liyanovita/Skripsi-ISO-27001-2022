<?php

namespace App\Services\Intelligence;

use App\Models\AssessmentSession;
use App\Services\Traits\MaturityHelper;
use App\Services\Traits\SessionLoader;
use App\Services\Traits\ResultCalculator;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    use MaturityHelper, SessionLoader, ResultCalculator;

    /**
     * Get tactical analytics data
     */
    public function getTacticalData(int $userId, ?int $selectedId): array
    {
        return $this->buildTacticalData($userId, $selectedId);
    }

    /**
     * Build tactical data
     */
    private function buildTacticalData(int $userId, ?int $selectedId): array
    {
        // Load all sessions with results and standards in one query
        $sessions = $this->loadUserSessions($userId, 'created_at', 'desc');

        $latestSession = $sessions->firstWhere('id', $selectedId) ?? $sessions->first();
        $previousSession = $sessions->where('id', '!=', $latestSession?->id)->first();
        $selectedId = $latestSession?->id;

        $comparison = null;
        $findings = collect();
        $stats = [
            'total_gaps'       => 0,
            'critical'         => 0,
            'compliant'        => 0,
            'partial'          => 0,
            'non_compliant'    => 0,
            'needs_improvement'=> 0,
            'unassessed'       => 0,
            'excluded'         => 0,
            'total_controls'   => 1,
            'scored'           => 0,
            'assessed'         => 0,
            /* legacy alias kept for safety */
            'total'            => 0,
            'strategic'        => 0,
        ];

        if ($latestSession) {
            $comparison = $this->buildComparison($latestSession, $previousSession);
            $results = $this->filterAssessableResults($latestSession->results);

            // Get findings using trait method
            $findings = $this->getFindings($results, 3);
            $complianceBreakdown = $this->calculateComplianceBreakdown($results);

            $scored = $results->where('status', 'completed');
            $stats = [
                'total_gaps' => $findings->count(),
                'critical' => $this->getCriticalFindings($results)->count(),
                'compliant' => $complianceBreakdown['compliant'],
                'partial' => $complianceBreakdown['partial'],
                'non_compliant' => $complianceBreakdown['non_compliant'],
                'needs_improvement' => $findings->count(),
                'unassessed' => $complianceBreakdown['unassessed'],
                'excluded' => $complianceBreakdown['excluded'] ?? 0,
                'total_controls' => max($results->count(), 1),
                'scored' => $scored->count(),
                'assessed' => $scored->count(),
            ];
        }

        return compact('sessions', 'latestSession', 'comparison', 'selectedId', 'findings', 'stats');
    }

    /**
     * Get strategic analytics data
     */
    public function getStrategicData(int $userId, ?int $selectedId): array
    {
        return $this->buildStrategicData($userId, $selectedId);
    }

    /**
     * Build strategic data
     */
    private function buildStrategicData(int $userId, ?int $selectedId): array
    {
        // Load all sessions with results and standards in one query
        $sessions = $this->loadUserSessions($userId, 'created_at', 'desc');

        $latestSession = $sessions->firstWhere('id', $selectedId) ?? $sessions->first();
        $previousSession = $sessions->where('id', '!=', $latestSession?->id)->first();
        $selectedId = $latestSession?->id;

        $comparison = null;
        $stats = ['total_gaps' => 0, 'critical' => 0, 'compliant' => 0, 'partial' => 0, 'non_compliant' => 0, 'needs_improvement' => 0, 'unassessed' => 0, 'excluded' => 0, 'total_controls' => 1];
        $maturityDistribution = [0, 0, 0, 0, 0];
        $complianceBreakdown = ['compliant' => 0, 'partial' => 0, 'non_compliant' => 0, 'unassessed' => 0, 'excluded' => 0];
        $maturityViews = ['global' => []];
        $maturityTrends = $sessions
            ->reverse()
            ->values()
            ->map(fn($session) => [
                'name' => $session->name,
                'overall_maturity_score' => $this->calculateSessionMaturityScore($session),
            ]);

        if ($latestSession) {
            $comparison = $this->buildComparison($latestSession, $previousSession);

            $results = $this->filterAssessableResults($latestSession->results);
            $findings = $this->getFindings($results, 3);

            // Calculate maturity distribution using trait method
            $maturityDistribution = array_values($this->calculateMaturityDistribution($results));

            // Calculate compliance breakdown using trait method
            $complianceBreakdown = $this->calculateComplianceBreakdown($results);

            // Build maturity views from comparison domains
            $maturityViews = [
                'global' => collect($comparison['domains'])->map(fn($domain) => [
                    'label' => $domain['label'],
                    'fullLabel' => $domain['label'],
                    'value' => $domain['latest'],
                ])->values()->all(),
            ];

            // Calculate stats
            $activeResults = $results->where('is_applicable', true);
            $stats = [
                'total_gaps' => $findings->count(),
                'critical' => $this->getCriticalFindings($results)->count(),
                'compliant' => $complianceBreakdown['compliant'],
                'partial' => $complianceBreakdown['partial'],
                'non_compliant' => $complianceBreakdown['non_compliant'],
                'needs_improvement' => $findings->count(),
                'unassessed' => $complianceBreakdown['unassessed'],
                'excluded' => $complianceBreakdown['excluded'] ?? 0,
                'total_controls' => $activeResults->count() ?: 1,
            ];
        }

        $isAiProcessing = $latestSession
            ? Cache::get("session_{$latestSession->id}_summary_status") === 'processing'
            : false;

        return compact('sessions', 'latestSession', 'comparison', 'selectedId', 'stats', 'maturityDistribution', 'complianceBreakdown', 'maturityViews', 'maturityTrends', 'isAiProcessing');
    }

    /**
     * Build comparison between two sessions
     */
    public function buildComparison(AssessmentSession $latest, ?AssessmentSession $prev): array
    {
        return [
            'latest_score' => $this->calculateSessionMaturityScore($latest),
            'previous_score' => $prev ? $this->calculateSessionMaturityScore($prev) : 0,
            'delta' => $this->calculateSessionMaturityScore($latest) - ($prev ? $this->calculateSessionMaturityScore($prev) : 0),
            'domains' => $this->calculateComparativeDomains($latest, $prev),
        ];
    }

    protected function calculateSessionMaturityScore(AssessmentSession $session): float
    {
        $score = $this->filterApplicableResults($this->filterAssessableResults($session->results))
            ->where('status', 'completed')
            ->where('maturity_rating', '>=', 0)
            ->avg('maturity_rating') ?? 0;

        return round((float) $score, 2);
    }

    /**
     * Calculate comparative domains using already-loaded results
     */
    protected function calculateComparativeDomains(AssessmentSession $latest, ?AssessmentSession $prev): array
    {
        $domains = [
            'Policies' => 'A.5',
            'People' => 'A.6',
            'Physical' => 'A.7',
            'Technology' => 'A.8',
            'Clauses (4-10)' => 'clausa',
        ];

        $latestResults = $this->filterApplicableResults($this->filterAssessableResults($latest->results))
            ->where('status', 'completed');
        $prevResults = $prev
            ? $this->filterApplicableResults($this->filterAssessableResults($prev->results))->where('status', 'completed')
            : collect();

        $stats = [];
        foreach ($domains as $label => $prefix) {
            if ($prefix === 'clausa') {
                $latestScore = $latestResults
                    ->filter(fn($r) => $this->isGovernanceResult($r))
                    ->avg('maturity_rating') ?? 0;
                $prevScore = $prev
                    ? $prevResults->filter(fn($r) => $this->isGovernanceResult($r))->avg('maturity_rating') ?? 0
                    : 0;
            } else {
                $latestScore = $latestResults
                    ->filter(fn($r) => $this->isAnnexDomainResult($r, $prefix))
                    ->avg('maturity_rating') ?? 0;
                $prevScore = $prev
                    ? $prevResults->filter(fn($r) => $this->isAnnexDomainResult($r, $prefix))->avg('maturity_rating') ?? 0
                    : 0;
            }

            $stats[] = [
                'label' => $label,
                'latest' => round($latestScore, 1),
                'previous' => round($prevScore, 1),
                'delta' => round($latestScore - $prevScore, 1),
            ];
        }

        return $stats;
    }

    private function isGovernanceResult($result): bool
    {
        return in_array($result->standard?->type, ['clause', 'clausa'], true);
    }

    private function isAnnexDomainResult($result, string $prefix): bool
    {
        $code = $result->standard?->code;

        return is_string($code) && ($code === $prefix || str_starts_with($code, $prefix . '.'));
    }

}
