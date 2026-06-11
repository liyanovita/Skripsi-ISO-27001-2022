<?php

namespace App\Services\Traits;

use Illuminate\Support\Collection;

/**
 * Result Calculator Trait
 * 
 * Provides common result calculation methods used across services
 */
trait ResultCalculator
{
    /**
     * Calculate statistics from results collection
     * 
     * @param Collection $results Collection of assessment results
     * @return array Array with calculated statistics
     */
    protected function calculateResultStats(Collection $results): array
    {
        $results = $this->filterAssessableResults($results);
        $activeResults = $this->filterApplicableResults($results);
        $answered = $activeResults->where('status', 'completed');
        $total = $activeResults->count();
        $answeredCount = $answered->count();
        $compliant = $answered->where('maturity_rating', '>=', 4)->count();
        $partial = $answered->whereBetween('maturity_rating', [2, 3])->count();
        $nonCompliant = $answered->where('maturity_rating', '<=', 1)->count();
        $unassessed = $total - $answeredCount;

        return [
            'total' => $total,
            'answered' => $answeredCount,
            'compliant' => $compliant,
            'partial' => $partial,
            'non_compliant' => $nonCompliant,
            'unassessed' => $unassessed,
            'excluded' => $results->where('is_applicable', false)->count(),
            'completion_percentage' => $total > 0 ? round(($answeredCount / $total) * 100) : 0,
            'compliance_percentage' => $answeredCount > 0 ? round(($compliant / $answeredCount) * 100) : 0,
        ];
    }

    /**
     * Calculate maturity distribution
     * 
     * @param Collection $results Collection of assessment results
     * @return array Array with distribution by maturity level (1-5)
     */
    protected function calculateMaturityDistribution(Collection $results): array
    {
        $answered = $this->filterApplicableResults($this->filterAssessableResults($results))->where('status', 'completed');

        return [
            1 => $answered->where('maturity_rating', 1)->count(),
            2 => $answered->where('maturity_rating', 2)->count(),
            3 => $answered->where('maturity_rating', 3)->count(),
            4 => $answered->where('maturity_rating', 4)->count(),
            5 => $answered->where('maturity_rating', 5)->count(),
        ];
    }

    /**
     * Calculate compliance breakdown
     * 
     * @param Collection $results Collection of assessment results
     * @return array Array with compliance breakdown
     */
    protected function calculateComplianceBreakdown(Collection $results): array
    {
        $results = $this->filterAssessableResults($results);
        $activeResults = $this->filterApplicableResults($results);
        $answered = $activeResults->where('status', 'completed');

        return [
            'compliant' => $answered->where('maturity_rating', '>=', 4)->count(),
            'partial' => $answered->whereBetween('maturity_rating', [2, 3])->count(),
            'non_compliant' => $answered->where('maturity_rating', '<=', 1)->count(),
            'unassessed' => $activeResults->where('status', '!=', 'completed')->count(),
            'excluded' => $results->where('is_applicable', false)->count(),
        ];
    }

    /**
     * Get findings (gaps) from results
     *
     * @param Collection $results Collection of assessment results
     * @param int $maxRating Maximum rating to consider as finding (default: 3)
     * @return Collection Findings collection
     */
    protected function getFindings(Collection $results, int $maxRating = 3): Collection
    {
        return $this->filterAssessableResults($results)
            ->filter(fn($r) => $r->is_applicable && $r->status === 'completed' && $r->maturity_rating <= $maxRating)
            ->sortBy(fn($r) => $r->standard->code ?? '', SORT_NATURAL | SORT_FLAG_CASE);
    }

    /**
     * Get critical findings (maturity_rating <= 1 or risk_level === 'Critical')
     *
     * @param Collection $results Collection of assessment results
     * @return Collection Critical findings
     */
    protected function getCriticalFindings(Collection $results): Collection
    {
        return $this->filterAssessableResults($results)
            ->filter(fn($r) => $r->is_applicable && $r->status === 'completed' && ($r->risk_level === 'Critical' || $r->maturity_rating <= 1))
            ->sortBy(fn($r) => $r->standard->code ?? '', SORT_NATURAL | SORT_FLAG_CASE);
    }

    /**
     * Get results with pending treatment
     *
     * @param Collection $results Collection of assessment results
     * @return Collection Results with pending treatment
     */
    protected function getResultsWithPendingTreatment(Collection $results): Collection
    {
        return $this->filterAssessableResults($results)->filter(fn($r) =>
            !empty($r->treatment_due_date) &&
            $r->is_applicable &&
            $r->status === 'completed' &&
            $r->maturity_rating < 4 &&
            $r->treatment_status !== 'closed'
        )->sortBy('treatment_due_date');
    }

    protected function filterAssessableResults(Collection $results): Collection
    {
        return $results->filter(fn($r) =>
            is_array($r->standard?->questions) &&
            count($r->standard->questions) > 0
        );
    }

    protected function filterApplicableResults(Collection $results): Collection
    {
        return $results->filter(fn($r) => $r->is_applicable);
    }
}
