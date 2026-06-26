<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\CommunityTemplate;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Core stats
        $totalUsers = User::count();
        $activeSessions = AssessmentSession::where('status', 'in_progress')->count();
        $completedSessions = AssessmentSession::where('status', 'completed')->count();
        $totalSessions = AssessmentSession::count();

        $averageScore = AssessmentSession::where('overall_maturity_score', '>', 0)
            ->avg('overall_maturity_score') ?? 0;

        // Recent items
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        $recentSessions = AssessmentSession::with('user')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // User growth per month (last 6 months)
        $userGrowth = User::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw("COUNT(*) as count")
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Session activity per month (last 6 months)
        $sessionActivity = AssessmentSession::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw("COUNT(*) as count")
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Fill missing months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }
        $userGrowthData = array_map(fn($m) => $userGrowth[$m] ?? 0, $months);
        $sessionActivityData = array_map(fn($m) => $sessionActivity[$m] ?? 0, $months);
        $monthLabels = array_map(fn($m) => \Carbon\Carbon::parse($m . '-01')->format('M Y'), $months);

        // Global maturity distribution
        $maturityDistribution = [
            AssessmentResult::where('maturity_rating', 1)->where('status', 'completed')->count(),
            AssessmentResult::where('maturity_rating', 2)->where('status', 'completed')->count(),
            AssessmentResult::where('maturity_rating', 3)->where('status', 'completed')->count(),
            AssessmentResult::where('maturity_rating', 4)->where('status', 'completed')->count(),
            AssessmentResult::where('maturity_rating', 5)->where('status', 'completed')->count(),
        ];

        // Organization sector distribution
        $sectorDistribution = User::whereNotNull('business_sector')
            ->where('business_sector', '!=', '')
            ->select('business_sector', DB::raw('COUNT(*) as count'))
            ->groupBy('business_sector')
            ->orderByDesc('count')
            ->take(6)
            ->pluck('count', 'business_sector')
            ->toArray();

        // Organization scale distribution
        $scaleDistribution = User::whereNotNull('organization_scale')
            ->where('organization_scale', '!=', '')
            ->select('organization_scale', DB::raw('COUNT(*) as count'))
            ->groupBy('organization_scale')
            ->orderByDesc('count')
            ->pluck('count', 'organization_scale')
            ->toArray();

        // Community stats
        $totalTemplates = CommunityTemplate::count();
        $totalDownloads = CommunityTemplate::sum('downloads_count');

        // Knowledge Base stats
        $totalArticles = \App\Models\KnowledgeBase::count();

        // Pending CAPA tasks
        $pendingCapa = AssessmentResult::whereNotNull('treatment_due_date')
            ->where('maturity_rating', '<', 4)
            ->where('maturity_rating', '>=', 0)
            ->where(function ($q) {
                $q->whereNull('treatment_status')
                  ->orWhereIn('treatment_status', ['open', 'in_progress']);
            })
            ->count();

        $overdueCapa = AssessmentResult::whereNotNull('treatment_due_date')
            ->where('treatment_due_date', '<', now()->toDateString())
            ->where('maturity_rating', '<', 4)
            ->where('maturity_rating', '>=', 0)
            ->where(function ($q) {
                $q->whereNull('treatment_status')
                  ->orWhereIn('treatment_status', ['open', 'in_progress']);
            })
            ->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeSessions',
            'completedSessions',
            'totalSessions',
            'averageScore',
            'recentUsers',
            'recentSessions',
            'monthLabels',
            'userGrowthData',
            'sessionActivityData',
            'maturityDistribution',
            'sectorDistribution',
            'scaleDistribution',
            'totalTemplates',
            'totalDownloads',
            'totalArticles',
            'pendingCapa',
            'overdueCapa'
        ));
    }
}
