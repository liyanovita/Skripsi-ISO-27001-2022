<?php

namespace App\Observers;

use App\Models\CommunityTemplate;

/**
 * Observer for CommunityTemplate model
 *
 * Cache invalidation is a no-op since Eloquent caching is disabled.
 * This observer is kept for future use.
 */
class CommunityTemplateObserver
{
    public function created(CommunityTemplate $template): void {}

    public function updated(CommunityTemplate $template): void {}

    public function deleted(CommunityTemplate $template): void {}
}