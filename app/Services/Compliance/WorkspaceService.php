<?php

namespace App\Services\Compliance;

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;

class WorkspaceService
{
    /**
     * Get workspace data with optimized queries
     * 
     * Optimization:
     * - Load sessions and results with eager loading
     * - Use database queries for filtering
     * - Minimize N+1 queries
     */
    public function getWorkspaceData(int $userId, ?int $selectedId): array
    {
        // Load sessions with results and standards in one query
        $sessions = AssessmentSession::with(['results.standard'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        $selectedSession = $sessions->firstWhere('id', $selectedId) ?? $sessions->first();

        $results = collect();
        if ($selectedSession) {
            // Use already-loaded results from eager loading
            $results = $selectedSession->results
                ->filter(fn($r) => $this->isAssessableResult($r))
                ->sortBy(fn($r) => $r->standard->code, SORT_NATURAL)
                ->values();
        }

        // Calculate stats from already-loaded results
        $stats = [
            'total'         => $results->count(),
            'gaps'          => $results->where('is_applicable', true)->where('status', 'completed')->whereNotNull('maturity_rating')->where('maturity_rating', '<', 4)->count(),
            'applicable'    => $results->where('is_applicable', true)->count(),
            'not_applicable'=> $results->where('is_applicable', false)->count(),
            'closed'        => $results
                ->where('is_applicable', true)
                ->where('status', 'completed')
                ->whereNotNull('maturity_rating')
                ->where('maturity_rating', '<', 4)
                ->where('treatment_status', 'closed')
                ->count(),
        ];

        return compact('sessions', 'selectedSession', 'selectedId', 'results', 'stats');
    }

    /**
     * Update a single workspace entry
     * 
     * Optimization:
     * - Verify ownership with single query
     * - Only update changed fields
     */
    public function updateEntry(int $resultId, int $userId, array $data): AssessmentResult
    {
        $result = AssessmentResult::with('standard')
            ->whereHas('session', fn($query) => $query->where('user_id', $userId))
            ->findOrFail($resultId);

        $updateData = [];

        if (array_key_exists('is_applicable', $data)) {
            $isClause = in_array($result->standard->type ?? '', ['clause', 'clausa']);
            $updateData['is_applicable'] = $isClause ? true : filter_var($data['is_applicable'], FILTER_VALIDATE_BOOLEAN);
        }
        if (array_key_exists('soa_justification', $data)) {
            $updateData['soa_justification'] = $data['soa_justification'];
        }
        if (array_key_exists('treatment_due_date', $data)) {
            $updateData['treatment_due_date'] = $data['treatment_due_date'] ?: null;
        }
        if (array_key_exists('treatment_pic', $data)) {
            $updateData['treatment_pic'] = $data['treatment_pic'];
        }
        if (array_key_exists('treatment_status', $data)) {
            $allowed = ['open', 'in_progress', 'closed'];
            if (in_array($data['treatment_status'], $allowed)) {
                $updateData['treatment_status'] = $data['treatment_status'];
            }
        }

        $result->update($updateData);

        return $result;
    }

    /**
     * Get SoA data with optimized queries
     * 
     * Optimization:
     * - Eager load relationships to avoid N+1
     */
    public function getSoaData(int $sessionId, int $userId): AssessmentSession
    {
        return AssessmentSession::with(['results.standard.parent'])
            ->where('user_id', $userId)
            ->findOrFail($sessionId);
    }

    protected function isAssessableResult(AssessmentResult $result): bool
    {
        return is_array($result->standard?->questions) && count($result->standard->questions) > 0;
    }
}
