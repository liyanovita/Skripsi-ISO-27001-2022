<?php

namespace App\Services\Traits;

use App\Models\AssessmentSession;
use Illuminate\Support\Collection;

/**
 * Session Loader Trait
 * 
 * Provides common session loading patterns to eliminate code duplication
 */
trait SessionLoader
{
    /**
     * Load user sessions with results and standards
     * 
     * @param int $userId User ID
     * @param string $orderBy Order by column (default: created_at)
     * @param string $direction Order direction (asc/desc)
     * @param int|null $limit Limit results (null = no limit)
     * @return Collection Collection of sessions
     */
    protected function loadUserSessions(
        int $userId,
        string $orderBy = 'created_at',
        string $direction = 'desc',
        ?int $limit = null
    ): Collection {
        $query = AssessmentSession::with(['results.standard'])
            ->where('user_id', $userId)
            ->orderBy($orderBy, $direction);

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

}
