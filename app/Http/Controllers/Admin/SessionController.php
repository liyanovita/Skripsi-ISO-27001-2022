<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Organization;
use App\Services\Assessment\SessionService;

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
        $draftSessions     = AssessmentSession::where('status', 'draft')->count();
        $activeSessions    = AssessmentSession::where('status', 'in_progress')->count();
        $completedSessions = AssessmentSession::where('status', 'completed')->count();

        $sessions = AssessmentSession::with(['user', 'organization'])
            ->withCount(['results', 'invitedUsers'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('organization', fn($q) => $q->where('name', 'like', "%{$search}%"));
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
            'totalSessions', 'draftSessions', 'activeSessions', 'completedSessions',
            'month'
        ));
    }

    public function show(AssessmentSession $session)
    {
        $session->load(['user', 'organization', 'invitedUsers', 'results.standard']);

        // Calculate stats
        $results    = $session->results;
        $assessable = $results->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0);
        $applicable = $assessable->filter(fn($r) => $r->is_applicable);
        $completed  = $applicable->where('status', 'completed');

        $stats = [
            'total_controls' => $assessable->count(),
            'applicable'     => $applicable->count(),
            'completed'      => $completed->count(),
            'compliant'      => $completed->where('maturity_rating', '>=', 4)->count(),
            'partial'        => $completed->filter(fn($r) => $r->maturity_rating >= 2 && $r->maturity_rating <= 3)->count(),
            'non_compliant'  => $completed->where('maturity_rating', '<=', 1)->count(),
            'excluded'       => $assessable->where('is_applicable', false)->count(),
            'completion_pct' => $applicable->count() > 0
                ? round(($completed->count() / $applicable->count()) * 100)
                : 0,
        ];

        $maturityDistribution = [
            $completed->where('maturity_rating', 1)->count(),
            $completed->where('maturity_rating', 2)->count(),
            $completed->where('maturity_rating', 3)->count(),
            $completed->where('maturity_rating', 4)->count(),
            $completed->where('maturity_rating', 5)->count(),
        ];

        $criticalFindings = $completed->filter(fn($r) => $r->maturity_rating <= 1)
            ->sortBy(fn($r) => $r->standard->code ?? '', SORT_NATURAL)
            ->values();

        return view('admin.sessions.show', compact(
            'session', 'stats', 'maturityDistribution', 'criticalFindings'
        ));
    }


    public function workspace(AssessmentSession $session)
    {
        $session->load(['user', 'organization', 'results.standard']);

        $results    = $session->results;
        $assessable = $results->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0);
        $applicable = $assessable->filter(fn($r) => $r->is_applicable);
        $completed  = $applicable->where('status', 'completed');

        $stats = [
            'total_controls' => $assessable->count(),
            'applicable'     => $applicable->count(),
            'completed'      => $completed->count(),
            'compliant'      => $completed->where('maturity_rating', '>=', 4)->count(),
            'partial'        => $completed->filter(fn($r) => $r->maturity_rating >= 2 && $r->maturity_rating <= 3)->count(),
            'non_compliant'  => $completed->where('maturity_rating', '<=', 1)->count(),
            'completion_pct' => $applicable->count() > 0
                ? round(($completed->count() / $applicable->count()) * 100)
                : 0,
        ];

        // Group results by parent clause for structured display
        $groupedResults = $assessable
            ->sortBy(fn($r) => $r->standard->code ?? '', SORT_NATURAL | SORT_FLAG_CASE)
            ->groupBy(fn($r) => $r->standard->parent?->code ?? $r->standard->code ?? 'Other');

        // CAPA items: results yang membutuhkan tindakan
        $capaItems = $completed->filter(fn($r) => $r->maturity_rating < 4)->sortBy('maturity_rating')->values();

        return view('admin.sessions.workspace', compact(
            'session', 'stats', 'groupedResults', 'capaItems'
        ));
    }

    public function create()
    {
        $users         = User::where('status', 'active')->orderBy('name', 'asc')->get();
        $organizations = Organization::orderBy('name', 'asc')->get();
        return view('admin.sessions.create', compact('users', 'organizations'));
    }

    public function store(Request $request, SessionService $sessionService)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'organization_id' => 'nullable|exists:organizations,id',
            'user_id'         => 'required|exists:users,id',
            'status'          => 'required|in:draft,in_progress,completed',
            'deadline'        => 'nullable|date',
            'invited_users'   => 'nullable|array',
            'invited_users.*' => 'exists:users,id',
        ]);

        $session = $sessionService->createSession([
            'user_id'         => $validated['user_id'],
            'organization_id' => $validated['organization_id'] ?? null,
            'name'            => $validated['name'],
            'status'          => $validated['status'],
            'deadline'        => $validated['deadline'] ?? null,
        ]);

        $syncData = [
            $validated['user_id'] => ['role' => 'lead']
        ];

        if (!empty($validated['invited_users'])) {
            foreach ($validated['invited_users'] as $invitedId) {
                if ($invitedId != $validated['user_id']) {
                    $syncData[$invitedId] = ['role' => 'auditor'];
                }
            }
        }

        $session->invitedUsers()->sync($syncData);

        // Notify assigned users
        $assignedUserIds = array_keys($syncData);
        $assignedUsers = User::whereIn('id', $assignedUserIds)->get();
        foreach ($assignedUsers as $user) {
            $user->notify(new \App\Notifications\AuditSessionAssignedNotification($session, auth()->user()));
        }

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Audit session created and assigned successfully.');
    }

    public function edit(AssessmentSession $session)
    {
        $users             = User::where('status', 'active')->orderBy('name', 'asc')->get();
        $organizations     = Organization::orderBy('name', 'asc')->get();
        $currentInvitedIds = $session->invitedUsers()
            ->wherePivot('role', 'auditor')
            ->pluck('users.id')
            ->toArray();

        return view('admin.sessions.edit', compact(
            'session', 'users', 'organizations', 'currentInvitedIds'
        ));
    }

    public function update(Request $request, AssessmentSession $session)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'organization_id' => 'nullable|exists:organizations,id',
            'user_id'         => 'required|exists:users,id',
            'status'          => 'required|in:draft,in_progress,completed,archive',
            'deadline'        => 'nullable|date',
            'invited_users'   => 'nullable|array',
            'invited_users.*' => 'exists:users,id',
        ]);

        if ($validated['status'] === 'archive') {
            $session->update([
                'name'            => $validated['name'],
                'organization_id' => $validated['organization_id'] ?? null,
                'user_id'         => $validated['user_id'],
                'deadline'        => $validated['deadline'] ?? null,
            ]);
            $session->delete();
        } else {
            if ($session->trashed()) {
                $session->restore();
            }
            $session->update([
                'name'            => $validated['name'],
                'organization_id' => $validated['organization_id'] ?? null,
                'user_id'         => $validated['user_id'],
                'status'          => $validated['status'],
                'deadline'        => $validated['deadline'] ?? null,
            ]);
        }

        $syncData = [
            $validated['user_id'] => ['role' => 'lead']
        ];

        if (!empty($validated['invited_users'])) {
            foreach ($validated['invited_users'] as $invitedId) {
                if ($invitedId != $validated['user_id']) {
                    $syncData[$invitedId] = ['role' => 'auditor'];
                }
            }
        }

        $session->invitedUsers()->sync($syncData);

        // Notify assigned users
        $assignedUserIds = array_keys($syncData);
        $assignedUsers = User::whereIn('id', $assignedUserIds)->get();
        foreach ($assignedUsers as $user) {
            $user->notify(new \App\Notifications\AuditSessionAssignedNotification($session, auth()->user()));
        }

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Audit session updated successfully.');
    }

    public function destroy(AssessmentSession $session)
    {
        $sessionName = $session->name;
        $session->invitedUsers()->detach();
        $session->results()->delete();
        $session->forceDelete();

        return redirect()->route('admin.sessions.index')
            ->with('success', "Session \"{$sessionName}\" deleted permanently.");
    }
}
