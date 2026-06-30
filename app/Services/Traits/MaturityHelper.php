<?php

namespace App\Services\Traits;

/**
 * Maturity Helper Trait
 * 
 * Provides common maturity-related utility methods used across services
 */
trait MaturityHelper
{
    /**
     * Get maturity level label based on score
     * 
     * @param float $score Maturity score (0-5)
     * @return string Maturity level label
     */
    public function getMaturityLabel(float $score): string
    {
        $score = round($score);
        if ($score <= 0) return __('Non-existent');
        if ($score == 1) return __('Initial');
        if ($score == 2) return __('Limited/Repeatable');
        if ($score == 3) return __('Defined');
        if ($score == 4) return __('Managed');
        return __('Optimized');
    }

    /**
     * Calculate compliance percentage from maturity score
     *
     * @param float $maturityScore Average maturity score (0-5)
     * @return int Compliance percentage (0-100)
     */
    public function calculateCompliancePercentage(float $maturityScore): int
    {
        return (int) round(($maturityScore / 5) * 100);
    }

    /**
     * Get compliance status based on maturity rating
     *
     * @param int $rating Maturity rating (0-5)
     * @return string Compliance status
     */
    public function getComplianceStatus(int $rating): string
    {
        if ($rating <= 0) return 'unassessed';
        if ($rating <= 1) return 'non_compliant';
        if ($rating <= 3) return 'partial';
        return 'compliant';
    }
}
