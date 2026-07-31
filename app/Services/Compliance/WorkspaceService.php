<?php

namespace App\Services\Compliance;

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\User;
use App\Mail\TaskAssignedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
        $user = auth()->user();
        // Load sessions with results and standards in one query (only completed sessions)
        $sessions = AssessmentSession::with(['results.standard'])
            ->when($user && !$user->isAdmin(), function($q) use ($userId, $user) {
                $q->where(function($sq) use ($userId, $user) {
                    $sq->where('user_id', $userId)
                       ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
                    if ($user?->organization_id) {
                        $sq->orWhere('organization_id', $user->organization_id);
                    }
                });
            })
            ->where('status', 'completed')
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

        $notApplicableCount = $results->where('is_applicable', false)->count();
        $applicableCount = max(0, 137 - $notApplicableCount);

        // Calculate stats: gaps = 91 applicable controls with maturity < 5, not_applicable = 3 excluded controls from SoA
        $gapsCount = $results->where('is_applicable', true)->where('status', 'completed')->whereNotNull('maturity_rating')->where('maturity_rating', '<', 5)->count();
        $stats = [
            'total'         => 137,
            'gaps'          => $gapsCount,
            'applicable'    => $applicableCount,
            'not_applicable'=> $notApplicableCount,
            'total_eval'    => $gapsCount + $notApplicableCount,
            'closed'        => $results
                ->where('is_applicable', true)
                ->where('status', 'completed')
                ->whereNotNull('maturity_rating')
                ->where('maturity_rating', '<', 5)
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
    public function updateEntry($resultId, int $userId, array $data): AssessmentResult
    {
        $id = $resultId instanceof AssessmentResult ? $resultId->id : (int) $resultId;
        $user = auth()->user();
        $query = AssessmentResult::with(['standard', 'session']);
        if (!$user || !$user->isAdmin()) {
            $query->whereHas('session', function($q) use ($userId, $user) {
                $q->where(function($sq) use ($userId, $user) {
                    $sq->where('user_id', $userId)
                       ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId))
                       ->orWhereHas('results', function($rq) use ($userId, $user) {
                           $rq->where('treatment_pic', (string) $userId)
                              ->when($user?->name, fn($nq) => $nq->orWhere('treatment_pic', $user->name));
                       });
                    if ($user?->organization_id) {
                        $sq->orWhere('organization_id', $user->organization_id);
                    }
                });
            });
        }
        $result = $query->findOrFail($id);

        if ($result->session && $result->session->isLockedForUser($user)) {
            abort(403, __('This audit session is locked for updates.'));
        }

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
        $assignedUser = null;
        if (array_key_exists('treatment_pic', $data)) {
            $newPic = $data['treatment_pic'];
            $oldPic = $result->treatment_pic;
            if ($newPic !== $oldPic) {
                $updateData['treatment_pic'] = $newPic;
                if (!empty($newPic)) {
                    $assignedUser = User::where('name', $newPic)->first();
                }
            }
        }
        if (array_key_exists('treatment_status', $data)) {
            $allowed = ['open', 'in_progress', 'closed'];
            if (in_array($data['treatment_status'], $allowed)) {
                $updateData['treatment_status'] = $data['treatment_status'];
            }
        }
        if (array_key_exists('notes', $data)) {
            $updateData['notes'] = $data['notes'];
        }
        if (request()->hasFile('evidence_file')) {
            $file = request()->file('evidence_file');
            $path = $file->store('evidence', 'public');
            $existing = is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]);
            $existing[] = $path;
            $updateData['evidence_file'] = array_values(array_unique($existing));
        }

        $result->update($updateData);

        if ($assignedUser) {
            try {
                Mail::to($assignedUser->email)->send(new TaskAssignedMail($result, $assignedUser));
            } catch (\Exception $e) {
                Log::error('Failed to send task assignment email: ' . $e->getMessage());
            }

            // Database & Email Notification
            try {
                $assignedUser->notify(new \App\Notifications\CorrectiveActionRequiredNotification($result, auth()->user() ?: $assignedUser));
            } catch (\Exception $e) {
                Log::error('Failed to send corrective action notification: ' . $e->getMessage());
            }
        }

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
        $user = auth()->user();
        $query = AssessmentSession::with(['results.standard.parent']);
        if (!$user || !$user->isAdmin()) {
            $query->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('invitedUsers', fn($iq) => $iq->where('user_id', $userId));
            });
        }
        return $query->findOrFail($sessionId);
    }

    protected function isAssessableResult(AssessmentResult $result): bool
    {
        return is_array($result->standard?->questions) && count($result->standard->questions) > 0;
    }
}
