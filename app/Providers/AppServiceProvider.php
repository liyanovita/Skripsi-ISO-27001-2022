<?php

namespace App\Providers;

use App\Models\AssessmentResult;
use App\Models\AuditTrail;
use App\Models\CommunityTemplate;
use App\Models\AssessmentSession;
use App\Models\KnowledgeBase;
use App\Observers\AssessmentResultObserver;
use App\Observers\CommunityTemplateObserver;
use App\Observers\AssessmentSessionObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for cache invalidation and audit logging
        AssessmentResult::observe(AssessmentResultObserver::class);
        CommunityTemplate::observe(CommunityTemplateObserver::class);
        AssessmentSession::observe(AssessmentSessionObserver::class);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Share sidebar badge counts with the app layout.
        // Using a View composer keeps the queries out of the blade template
        // and runs them only once per request for the layouts.app view.
        View::composer('layouts.app', function ($view) {
            if (!Auth::check()) {
                $view->with([
                    'sidebarInProgressSessions' => 0,
                    'sidebarOpenGaps'            => 0,
                    'sidebarKbCustomCount'       => 0,
                    'sidebarCommunityCount'      => 0,
                    'sidebarTodayTrail'          => 0,
                ]);
                return;
            }

            $userId = Auth::id();

            $view->with([
                'sidebarInProgressSessions' => AssessmentSession::forUser($userId)->inProgress()->count(),
                'sidebarOpenGaps'           => AssessmentResult::whereHas('session', fn($q) => $q->where('user_id', $userId))
                                                    ->where('maturity_rating', '<', 4)
                                                    ->where('maturity_rating', '>', 0)
                                                    ->where('treatment_status', 'open')
                                                    ->count(),
                'sidebarKbCustomCount'      => KnowledgeBase::custom()->count(),
                'sidebarCommunityCount'     => CommunityTemplate::count(),
                'sidebarTodayTrail'         => AuditTrail::forUser($userId)->whereDate('created_at', today())->count(),
            ]);
        });
    }
}
