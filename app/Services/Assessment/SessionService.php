<?php

namespace App\Services\Assessment;

use App\Models\AssessmentSession;
use App\Models\IsoStandard;
use App\Models\AssessmentResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SessionService
{
    public function getUserSessions(int $userId): Collection
    {
        return AssessmentSession::withCount([
            'results' => function ($query) {
                $query->where('is_applicable', true)
                    ->whereHas('standard', function ($standardQuery) {
                        $standardQuery->whereNotNull('questions');
                    });
            },
            'results as answered_count' => function ($query) {
                $query->where('is_applicable', true)
                    ->where('status', 'completed')
                    ->whereHas('standard', function ($standardQuery) {
                        $standardQuery->whereNotNull('questions');
                    });
            }
        ])
            ->where('user_id', $userId)
            ->withTrashed()
            ->latest()
            ->get();
    }

    public function getSession(int $id, int $userId): AssessmentSession
    {
        return AssessmentSession::with(['results.standard'])
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function createSession(array $data): AssessmentSession
    {
        return DB::transaction(function () use ($data) {
            $session = AssessmentSession::create([
                'user_id' => $data['user_id'],
                'name' => $data['name'],
                'status' => 'in_progress',
            ]);

            $this->initializeResults($session);

            return $session;
        });
    }

    public function updateSession(int $id, int $userId, array $data): AssessmentSession
    {
        $session = AssessmentSession::where('user_id', $userId)->findOrFail($id);
        $session->update($data);
        
        return $session;
    }

    public function deleteSession(int $id, int $userId): bool
    {
        $session = AssessmentSession::where('user_id', $userId)->findOrFail($id);
        return $session->delete();
    }

    public function restoreSession(int $id, int $userId): bool
    {
        $session = AssessmentSession::where('user_id', $userId)
            ->withTrashed()
            ->findOrFail($id);
        return $session->restore();
    }

    public function forceDeleteSession(int $id, int $userId): bool
    {
        $session = AssessmentSession::where('user_id', $userId)
            ->withTrashed()
            ->findOrFail($id);
        return $session->forceDelete();
    }

    public function cloneSession(int $id, int $userId): AssessmentSession
    {
        $original = AssessmentSession::with('results')
            ->where('user_id', $userId)
            ->findOrFail($id);

        return DB::transaction(function () use ($original, $userId) {
            $newSession = AssessmentSession::create([
                'user_id' => $userId,
                'name' => $original->name . ' (Copy)',
                'status' => 'in_progress',
                'overall_maturity_score' => $original->overall_maturity_score
            ]);

            foreach ($original->results as $result) {
                AssessmentResult::create([
                    'session_id' => $newSession->id,
                    'iso_standard_id' => $result->iso_standard_id,
                    'maturity_rating' => $result->maturity_rating,
                    'answers' => $result->answers ?? [],
                    'notes' => $result->notes,
                    'evidence_file' => $result->evidence_file,
                    'ai_recommendation' => $result->ai_recommendation,
                    'corrective_action_plan' => $result->corrective_action_plan,
                    'risk_priority' => $result->risk_priority,
                    'control_insight' => $result->control_insight,
                    'evidence_validation' => $result->evidence_validation,
                    'is_applicable' => $result->is_applicable,
                    'soa_justification' => $result->soa_justification,
                    'implementation_status' => $result->implementation_status,
                    'treatment_due_date' => $result->treatment_due_date,
                    'treatment_pic' => $result->treatment_pic,
                    'treatment_status' => $result->treatment_status,
                    'status' => $result->status
                ]);
            }

            return $newSession;
        });
    }

    public function finalizeSession(int $id, int $userId): AssessmentSession
    {
        $session = AssessmentSession::with('results.standard')
            ->where('user_id', $userId)
            ->findOrFail($id);

        $assessableResults = $this->getAssessableResults($session);
        $applicableAssessable = $assessableResults->filter(fn($r) => $r->is_applicable);
        $total = $applicableAssessable->count();
        $completed = $applicableAssessable->where('status', 'completed')->count();

        if ($total !== $completed) {
            throw new \Exception("Cannot finalize: {$completed}/{$total} applicable controls scored. Please score all applicable controls first.");
        }

        $session->update(['status' => 'completed']);

        return $session;
    }

    public function exportSessionToJson(int $id): array
    {
        $session = AssessmentSession::with('results.standard')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        return [
            'app' => 'OpenAudit-27001:2022',
            'version' => '1.0.0',
            'exported_at' => now()->toIso8601String(),
            'session' => [
                'name' => $session->name . ' (Shared)',
                'overall_maturity_score' => $session->overall_maturity_score,
                'results' => $session->results->map(function($r) {
                    return [
                        'iso_code' => $r->standard->code,
                        'maturity_rating' => $r->maturity_rating,
                        'answers' => $r->answers,
                        'notes' => $r->notes,
                        'evidence_file' => $r->evidence_file,
                        'ai_recommendation' => $r->ai_recommendation,
                        'corrective_action_plan' => $r->corrective_action_plan,
                        'risk_priority' => $r->risk_priority,
                        'control_insight' => $r->control_insight,
                        'evidence_validation' => $r->evidence_validation,
                        'is_applicable' => $r->is_applicable,
                        'soa_justification' => $r->soa_justification,
                        'implementation_status' => $r->implementation_status,
                        'treatment_due_date' => $r->treatment_due_date ? $r->treatment_due_date->format('Y-m-d') : null,
                        'treatment_pic' => $r->treatment_pic,
                        'treatment_status' => $r->treatment_status,
                        'status' => $r->status
                    ];
                })
            ]
        ];
    }

    public function importSessionFromJson(array $data, int $userId, ?string $name = null): AssessmentSession
    {
        if (!isset($data['session'])) {
            throw new \Exception('Invalid JSON file format or not an OpenAudit export.');
        }

        return DB::transaction(function () use ($data, $userId, $name) {
            $sessionData = $data['session'];
            
            $newSession = AssessmentSession::create([
                'user_id' => $userId,
                'name' => $name ?? $sessionData['name'] . ' (Imported)',
                'status' => 'in_progress',
                'overall_maturity_score' => $sessionData['overall_maturity_score'] ?? 0
            ]);

            $templateResults = collect($sessionData['results']);
            $standards = IsoStandard::all();

            foreach ($standards as $standard) {
                $res = $templateResults->firstWhere('iso_code', $standard->code);
                
                AssessmentResult::create([
                    'session_id' => $newSession->id,
                    'iso_standard_id' => $standard->id,
                    'maturity_rating' => $res['maturity_rating'] ?? 0,
                    'answers' => $res['answers'] ?? [],
                    'notes' => $res['notes'] ?? '',
                    'evidence_file' => $res['evidence_file'] ?? null,
                    'ai_recommendation' => $res['ai_recommendation'] ?? '',
                    'corrective_action_plan' => $res['corrective_action_plan'] ?? null,
                    'risk_priority' => $res['risk_priority'] ?? null,
                    'control_insight' => $res['control_insight'] ?? null,
                    'evidence_validation' => $res['evidence_validation'] ?? null,
                    'is_applicable' => $res['is_applicable'] ?? true,
                    'soa_justification' => $res['soa_justification'] ?? null,
                    'implementation_status' => $res['implementation_status'] ?? 'not_started',
                    'treatment_due_date' => $res['treatment_due_date'] ?? null,
                    'treatment_pic' => $res['treatment_pic'] ?? null,
                    'treatment_status' => $res['treatment_status'] ?? 'open',
                    'status' => $res ? ($res['status'] ?? 'not_started') : 'not_started'
                ]);
            }

            return $newSession;
        });
    }

    public function getMissingScores(AssessmentSession $session): array
    {
        $missingScores = $this->getAssessableResults($session)->filter(function ($result) {
            return $result->is_applicable && $result->status !== 'completed';
        });

        return [
            'codes' => $missingScores->pluck('standard.code')->toArray(),
            'count' => $missingScores->count()
        ];
    }

    public function getAssessmentProgress(AssessmentSession $session): array
    {
        $assessableResults = $this->getAssessableResults($session);
        $applicableAssessable = $assessableResults->filter(fn($r) => $r->is_applicable);
        $total = $applicableAssessable->count();
        $completed = $applicableAssessable->where('status', 'completed')->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    protected function getAssessableResults(AssessmentSession $session): Collection
    {
        return $session->results->filter(function ($result) {
            return is_array($result->standard?->questions) && count($result->standard->questions) > 0;
        });
    }

    protected function initializeResults(AssessmentSession $session): void
    {
        $standards = IsoStandard::all();
        
        foreach ($standards as $standard) {
            AssessmentResult::create([
                'session_id' => $session->id,
                'iso_standard_id' => $standard->id,
                'maturity_rating' => 0,
                'status' => 'not_started',
                'answers' => [],
            ]);
        }
    }
}
