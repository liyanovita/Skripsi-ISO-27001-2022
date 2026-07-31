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
        $hasCompletedSessions = \App\Models\AssessmentSession::where('status', 'completed')->exists();

        $baseQuery = AssessmentResult::where('is_applicable', true)
            ->whereNotNull('maturity_rating')
            ->where('maturity_rating', '<', 5)
            ->where('maturity_rating', '>=', 0);

        if ($request->filled('session_id')) {
            $baseQuery->where('session_id', $request->session_id);
        } else {
            $baseQuery->whereHas('session', function($q) {
                $q->where('status', 'completed');
            });
        }

        // Compute summary counts: totalCapa = 91 (applicable gaps requiring remediation)
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

        $excludedBaseQuery = AssessmentResult::where('is_applicable', false);
        if ($request->filled('session_id')) {
            $excludedBaseQuery->where('session_id', $request->session_id);
        } else {
            $excludedBaseQuery->whereHas('session', function($q) {
                $q->where('status', 'completed');
            });
        }
        $excludedCount = $excludedBaseQuery->count();

        // Apply filters
        $query = AssessmentResult::with(['session.user', 'session.organization', 'standard']);

        if ($request->status === 'excluded') {
            $query->where('is_applicable', false);
        } else {
            $query->where('is_applicable', true)
                ->whereNotNull('maturity_rating')
                ->where('maturity_rating', '<', 5)
                ->where('maturity_rating', '>=', 0);
        }

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        } else {
            $query->whereHas('session', function($q) {
                $q->where('status', 'completed');
            });
        }

        if ($request->filled('status') && $request->status !== 'excluded') {
            if ($request->status == 'pending') {
                $query->where(function ($q) {
                    $q->whereIn('treatment_status', ['open', 'in_progress'])
                      ->orWhereNull('treatment_status');
                });
            } elseif ($request->status == 'overdue') {
                $query->whereNotNull('treatment_due_date')
                      ->where('treatment_due_date', '<', now()->toDateString())
                      ->where(function ($q) {
                          $q->whereNull('treatment_status')
                            ->orWhere('treatment_status', '!=', 'completed');
                      });
            } else {
                $query->where('treatment_status', $request->status);
            }
        }

        if ($request->filled('risk')) {
            $risk = $request->risk;
            $query->where(function ($q) use ($risk) {
                $q->where('risk_priority', $risk);
                
                if ($risk === 'High') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNull('risk_priority')
                           ->where('maturity_rating', '<=', 2);
                    });
                } elseif ($risk === 'Medium') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNull('risk_priority')
                           ->where('maturity_rating', 3);
                    });
                } elseif ($risk === 'Low') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNull('risk_priority')
                           ->where('maturity_rating', '>=', 4);
                    });
                }
            });
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

        // Get excluded capas for Kanban matrix column
        $excludedQueryAll = AssessmentResult::with(['session.user', 'session.organization', 'standard'])
            ->where('is_applicable', false);
        if ($request->filled('session_id')) {
            $excludedQueryAll->where('session_id', $request->session_id);
        } else {
            $excludedQueryAll->whereHas('session', function($q) {
                $q->where('status', 'completed');
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $excludedQueryAll->where(function ($q2) use ($search) {
                $q2->whereHas('standard', function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
                })->orWhereHas('session.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }
        $excludedCapas = $excludedQueryAll->get();

        // Get unpaginated collection for Kanban BEFORE paginate mutates the query builder with limit 15
        $allCapas = (clone $query)->orderByRaw('CASE WHEN treatment_due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('treatment_due_date', 'asc')
            ->get();

        $capas = $query->orderByRaw('CASE WHEN treatment_due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('treatment_due_date', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Load the session context if filtered
        $filteredSession = null;
        if ($request->filled('session_id')) {
            $filteredSession = \App\Models\AssessmentSession::with('user')->find($request->session_id);
        }

        // Fetch only completed sessions for the filter dropdown
        $sessions = \App\Models\AssessmentSession::with('user')->where('status', 'completed')->orderBy('name', 'asc')->get();

        return view('admin.capa.index', compact(
            'capas', 'allCapas', 'totalCapa', 'openCount', 'inProgressCount', 'completedCount', 'overdueCount', 'excludedCount', 'excludedCapas', 'filteredSession', 'sessions'
        ));
    }

    public function edit(AssessmentResult $capa)
    {
        $capa->load(['session.user', 'session.invitedUsers', 'standard']);
        
        // Retrieve session lead/owner and all invited users for the session
        $sessionUsers = collect();
        if ($capa->session && $capa->session->user) {
            $sessionUsers->push($capa->session->user);
        }
        if ($capa->session && $capa->session->relationLoaded('invitedUsers')) {
            foreach ($capa->session->invitedUsers as $invited) {
                $sessionUsers->push($invited);
            }
        }
        $sessionUsers = $sessionUsers->unique('id');

        $history = \App\Models\AuditTrail::with('user')
            ->forModel(get_class($capa), $capa->id)
            ->latest()
            ->get();

        return view('admin.capa.edit', compact('capa', 'history', 'sessionUsers'));
    }

    public function update(Request $request, AssessmentResult $capa)
    {
        $validated = $request->validate([
            'treatment_status' => 'required|in:open,in_progress,completed',
            'treatment_due_date' => 'nullable|date',
            'treatment_pic' => 'nullable|string|max:255',
            'corrective_action_plan_text' => 'nullable|string',
            'risk_priority' => 'nullable|in:Low,Medium,High',
            'treatment_progress' => 'required|integer|min:0|max:100',
            'evidence_after_improvement' => 'nullable|string',
            'evidence_after_file' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip,rar,xlsx,xls|max:10240',
            'maturity_rating' => 'nullable|integer|between:0,5',
        ]);

        if ($capa->session && !auth()->user()->isAdmin() && ($capa->session->isLockedForUser(auth()->user()) || $capa->session->status === 'completed')) {
            return redirect()->back()->with('error', __('This audit session is completed and locked. Modifications are disabled.'));
        }

        $capa->treatment_status = $validated['treatment_status'];
        $capa->treatment_due_date = $validated['treatment_due_date'];
        $capa->treatment_pic = $validated['treatment_pic'];
        if (!empty($validated['risk_priority'])) {
            $capa->risk_priority = $validated['risk_priority'];
        }
        $capa->treatment_progress = $validated['treatment_progress'];

        // Handle document file upload for post-improvement evidence
        if ($request->hasFile('evidence_after_file')) {
            $file = $request->file('evidence_after_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $cleanName = \Illuminate\Support\Str::slug($originalName) ?: 'evidence';
            $fileName = $cleanName . '-' . time() . '.' . $extension;
            $path = $file->storeAs('capa_evidence', $fileName, 'public');
            $fileTag = "[Document] " . $path;
            if (!empty($validated['evidence_after_improvement'])) {
                $capa->evidence_after_improvement = $fileTag . "\n" . $validated['evidence_after_improvement'];
            } else {
                $capa->evidence_after_improvement = $fileTag;
            }
        } elseif (array_key_exists('evidence_after_improvement', $validated)) {
            $capa->evidence_after_improvement = $validated['evidence_after_improvement'];
        }

        if (isset($validated['maturity_rating'])) {
            $capa->maturity_rating = (int) $validated['maturity_rating'];
        }
        
        $planText = !empty($validated['corrective_action_plan_text']) ? $validated['corrective_action_plan_text'] : ($capa->ai_recommendation ?: 'Remediation plan generated from AI assessment.');

        // Save plan text into corrective_action_plan array
        $plan = $capa->corrective_action_plan ?? [];
        if (!is_array($plan)) {
            $plan = [];
        }
        $plan['action'] = $planText;
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
        $hasCompletedSessions = \App\Models\AssessmentSession::where('status', 'completed')->exists();

        $query = AssessmentResult::with(['session.user', 'session.organization', 'standard'])
            ->where('is_applicable', true)
            ->whereNotNull('maturity_rating')
            ->where('maturity_rating', '<', 5)
            ->where('maturity_rating', '>=', 0);

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        } else {
            $query->whereHas('session', function($q) {
                $q->where('status', 'completed');
            });
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
            $risk = $request->risk;
            $query->where(function ($q) use ($risk) {
                $q->where('risk_priority', $risk);
                
                if ($risk === 'High') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNull('risk_priority')
                           ->where('maturity_rating', '<=', 2);
                    });
                } elseif ($risk === 'Medium') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNull('risk_priority')
                           ->where('maturity_rating', 3);
                    });
                } elseif ($risk === 'Low') {
                    $q->orWhere(function ($q2) {
                        $q2->whereNull('risk_priority')
                           ->where('maturity_rating', '>=', 4);
                    });
                }
            });
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
