<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $search       = $request->input('search');
        $statusFilter = $request->input('status');
        $userFilter   = $request->input('user_id');
        $month        = $request->input('month');

        // Stats for KPI cards
        $totalSessions     = AssessmentSession::count();
        $activeSessions    = AssessmentSession::where('status', 'in_progress')->count();
        $completedSessions = AssessmentSession::where('status', 'completed')->count();

        $sessions = AssessmentSession::with('user')
            ->withCount('results')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->when($statusFilter, function ($q, $statusFilter) {
                if ($statusFilter === 'archive') {
                    return $q->onlyTrashed();
                }
                return $q->where('status', $statusFilter);
            })
            ->when($userFilter, fn($q) => $q->where('user_id', $userFilter))
            ->when($month, function ($q, $month) {
                $parts = explode('-', $month);
                if (count($parts) === 2) {
                    $q->whereYear('created_at', $parts[0])
                      ->whereMonth('created_at', $parts[1]);
                }
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sessions.index', compact(
            'sessions', 'search', 'statusFilter', 'userFilter',
            'totalSessions', 'activeSessions', 'completedSessions',
            'month'
        ));
    }

    public function show(AssessmentSession $session)
    {
        $session->load(['user', 'results.standard']);

        // Calculate stats
        $results = $session->results;
        $assessable = $results->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0);
        $applicable = $assessable->filter(fn($r) => $r->is_applicable);
        $completed = $applicable->where('status', 'completed');

        $stats = [
            'total_controls' => $assessable->count(),
            'applicable' => $applicable->count(),
            'completed' => $completed->count(),
            'compliant' => $completed->where('maturity_rating', '>=', 4)->count(),
            'partial' => $completed->filter(fn($r) => $r->maturity_rating >= 2 && $r->maturity_rating <= 3)->count(),
            'non_compliant' => $completed->where('maturity_rating', '<=', 1)->count(),
            'excluded' => $assessable->where('is_applicable', false)->count(),
            'completion_pct' => $applicable->count() > 0
                ? round(($completed->count() / $applicable->count()) * 100)
                : 0,
        ];

        // Maturity distribution for chart
        $maturityDistribution = [
            $completed->where('maturity_rating', 1)->count(),
            $completed->where('maturity_rating', 2)->count(),
            $completed->where('maturity_rating', 3)->count(),
            $completed->where('maturity_rating', 4)->count(),
            $completed->where('maturity_rating', 5)->count(),
        ];

        // Critical findings
        $criticalFindings = $completed->filter(fn($r) => $r->maturity_rating <= 1)
            ->sortBy(fn($r) => $r->standard->code ?? '', SORT_NATURAL)
            ->values();

        return view('admin.sessions.show', compact(
            'session', 'stats', 'maturityDistribution', 'criticalFindings'
        ));
    }

    public function destroy(AssessmentSession $session)
    {
        $sessionName = $session->name;
        $session->results()->delete();
        $session->forceDelete();

        return redirect()->route('admin.sessions.index')
            ->with('success', "Session \"{$sessionName}\" deleted permanently.");
    }
}
