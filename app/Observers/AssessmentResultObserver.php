<?php

namespace App\Observers;

use App\Models\AssessmentResult;

/**
 * Observer for AssessmentResult model
 *
 * Cache invalidation is a no-op since Eloquent caching is disabled
 * to avoid serialization issues. This observer is kept for future use.
 */
class AssessmentResultObserver
{
    public function created(AssessmentResult $result): void {}

    public function updated(AssessmentResult $result): void {}

    public function deleted(AssessmentResult $result): void {}

    public function restored(AssessmentResult $result): void {}
}