<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentResult;
use App\Models\AuditTrail;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;

class CapaController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = AssessmentResult::where(function ($q) {
            // CAPA is relevant for items with priority risk OR non-compliant OR explicitly scheduled
            $q->where('maturity_rating', '<', 4)
              ->where('maturity_rating', '>=', 0)
              ->orWhereNotNull('treatment_due_date')
              ->orWhereNotNull('treatment_status');
        });

        if ($request->filled('session_id')) {
            $baseQuery->where('session_id', $request->session_id);
        }

        // Compute summary counts
        $totalCapa = (clone $baseQuery)->count();
        $openCount = (clone $baseQuery)->where(function($q) {
            $q->where('treatment_status', 'open')
              ->orWhereNull('treatment_status');
        })->count();
        $inProgressCount = (clone $baseQuery)->where('treatment_status', 'in_progress')->count();
        $completedCount = (clone $baseQuery)->where('treatment_status', 'completed')->count();
        $overdueCount = (clone $baseQuery)->whereNotNull('treatment_due_date')
            ->where('treatment_due_date', '<', now()->toDateString())
            ->where('treatment_status', '!=', 'completed')
            ->count();

        // Apply filters
        $query = AssessmentResult::with(['session.user', 'session.organization', 'standard'])
            ->where(function ($q) {
                $q->where('maturity_rating', '<', 4)
                  ->where('maturity_rating', '>=', 0)
                  ->orWhereNotNull('treatment_due_date')
                  ->orWhereNotNull('treatment_status');
            });

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->filled('status')) {
            if ($request->status == 'pending') {
                $query->where(function ($q) {
                    $q->whereIn('treatment_status', ['open', 'in_progress'])
                      ->orWhereNull('treatment_status');
                });
            } else {
                $query->where('treatment_status', $request->status);
            }
        }

        if ($request->filled('risk')) {
            $query->where('risk_priority', $request->risk);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q2) use ($search) {
                $q2->whereHas('standard', function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
                })->orWhereHas('session.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        $capas = $query->orderByRaw('CASE WHEN treatment_due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('treatment_due_date', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Load the session context if filtered
        $filteredSession = null;
        if ($request->filled('session_id')) {
            $filteredSession = \App\Models\AssessmentSession::with('user')->find($request->session_id);
        }

        // Fetch all sessions for the session filter dropdown
        $sessions = \App\Models\AssessmentSession::with('user')->orderBy('name', 'asc')->get();

        return view('admin.capa.index', compact(
            'capas', 'totalCapa', 'openCount', 'inProgressCount', 'completedCount', 'overdueCount', 'filteredSession', 'sessions'
        ));
    }

    public function edit(AssessmentResult $capa)
    {
        $capa->load(['session.user', 'standard']);
        $history = \App\Models\AuditTrail::with('user')
            ->forModel(get_class($capa), $capa->id)
            ->latest()
            ->get();
        return view('admin.capa.edit', compact('capa', 'history'));
    }

    public function update(Request $request, AssessmentResult $capa)
    {
        $validated = $request->validate([
            'treatment_status' => 'required|in:open,in_progress,completed',
            'treatment_due_date' => 'nullable|date',
            'treatment_pic' => 'nullable|string|max:255',
            'corrective_action_plan_text' => 'required|string',
            'risk_priority' => 'required|in:Low,Medium,High',
            'treatment_progress' => 'required|integer|min:0|max:100',
            'evidence_after_improvement' => 'nullable|string',
        ]);

        $capa->treatment_status = $validated['treatment_status'];
        $capa->treatment_due_date = $validated['treatment_due_date'];
        $capa->treatment_pic = $validated['treatment_pic'];
        $capa->risk_priority = $validated['risk_priority'];
        $capa->treatment_progress = $validated['treatment_progress'];
        $capa->evidence_after_improvement = $validated['evidence_after_improvement'];
        
        // Save plan text into corrective_action_plan array
        $plan = $capa->corrective_action_plan ?? [];
        if (!is_array($plan)) {
            $plan = [];
        }
        $plan['action'] = $validated['corrective_action_plan_text'];
        $plan['last_updated_by'] = auth()->user()->name;
        $plan['updated_at'] = now()->toDateTimeString();
        
        $capa->corrective_action_plan = $plan;
        $capa->save();

        // Notify session owner, collaborators, and PIC
        $usersToNotify = collect();

        if ($capa->session && $capa->session->user) {
            $usersToNotify->push($capa->session->user);
        }

        if ($capa->session && $capa->session->invitedUsers) {
            foreach ($capa->session->invitedUsers as $collaborator) {
                $usersToNotify->push($collaborator);
            }
        }

        if (!empty($validated['treatment_pic'])) {
            $matchingUser = \App\Models\User::where('name', 'like', $validated['treatment_pic'])->first();
            if ($matchingUser) {
                $usersToNotify->push($matchingUser);
            }
        }

        $usersToNotify = $usersToNotify->unique('id');

        foreach ($usersToNotify as $user) {
            try {
                $user->notify(new \App\Notifications\CorrectiveActionRequiredNotification($capa, auth()->user()));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send CAPA notification to ' . $user->name . ': ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.capa.index')->with('success', 'Improvement Tracking updated successfully.');
    }

    public function exportCsv(Request $request)
    {
        $query = AssessmentResult::with(['session.user', 'session.organization', 'standard'])
            ->where(function ($q) {
                $q->where('maturity_rating', '<', 4)
                  ->where('maturity_rating', '>=', 0)
                  ->orWhereNotNull('treatment_due_date')
                  ->orWhereNotNull('treatment_status');
            });

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->filled('status')) {
            if ($request->status == 'pending') {
                $query->where(function ($q) {
                    $q->whereIn('treatment_status', ['open', 'in_progress'])
                      ->orWhereNull('treatment_status');
                });
            } else {
                $query->where('treatment_status', $request->status);
            }
        }

        if ($request->filled('risk')) {
            $query->where('risk_priority', $request->risk);
        }

        $capas = $query->orderByRaw('CASE WHEN treatment_due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('treatment_due_date', 'asc')
            ->get();

        $headers = [
            'User Name', 'Organization', 'Session Name', 'ISO Code', 'Control Title',
            'Maturity Rating', 'Risk Priority', 'Recommendation', 'Action Plan', 
            'PIC', 'Due Date', 'Status', 'Progress', 'Evidence After Improvement', 'Last Updated',
        ];

        $rows = [];
        foreach ($capas as $capa) {
            $plan     = $capa->corrective_action_plan;
            $planText = is_array($plan) ? ($plan['action'] ?? '-') : ($plan ?? '-');
            $rows[] = [
                $capa->session->user->name ?? 'N/A',
                $capa->session->organization?->name ?? 'N/A',
                $capa->session->name ?? 'N/A',
                $capa->standard->code ?? 'N/A',
                $capa->standard->title ?? 'N/A',
                $capa->maturity_rating,
                $capa->risk_priority ?? 'N/A',
                $capa->ai_recommendation ?? '-',
                $planText,
                $capa->treatment_pic ?? '-',
                $capa->treatment_due_date ? $capa->treatment_due_date->format('Y-m-d') : 'N/A',
                $capa->treatment_status ?? 'open',
                ($capa->treatment_progress ?? 0) . '%',
                $capa->evidence_after_improvement ?? '-',
                $capa->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        $filename = 'improvement_tracking_' . date('Y-m-d') . '.xlsx';
        return ExcelExportService::download($filename, $headers, $rows, 'Improvement Tracking');
    }
}
