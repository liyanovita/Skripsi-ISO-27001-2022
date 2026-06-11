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
                $query->whereHas('standard', function ($standardQuery) {
                    $standardQuery->whereNotNull('questions');
                });
            },
            'results as answered_count' => function ($query) {
                $query->where('status', 'completed')
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
                'status' => 'draft',
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
                'status' => 'draft',
                'overall_maturity_score' => $original->overall_maturity_score
            ]);

            foreach ($original->results as $result) {
                AssessmentResult::create([
                    'session_id' => $newSession->id,
                    'iso_standard_id' => $result->iso_standard_id,
                    'maturity_rating' => $result->maturity_rating,
                    'answers' => $result->answers ?? [],
                    'notes' => $result->notes,
                    'ai_recommendation' => $result->ai_recommendation,
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
        $total = $assessableResults->count();
        $completed = $assessableResults->where('status', 'completed')->count();

        if ($total !== $completed) {
            throw new \Exception("Cannot finalize: {$completed}/{$total} controls scored. Please score all controls first.");
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
                        'ai_recommendation' => $r->ai_recommendation,
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
                'status' => 'draft',
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
                    'ai_recommendation' => $res['ai_recommendation'] ?? '',
                    'status' => $res ? ($res['status'] ?? 'not_started') : 'not_started'
                ]);
            }

            return $newSession;
        });
    }

    public function getMissingScores(AssessmentSession $session): array
    {
        $missingScores = $this->getAssessableResults($session)->filter(function ($result) {
            return $result->status !== 'completed';
        });

        return [
            'codes' => $missingScores->pluck('standard.code')->toArray(),
            'count' => $missingScores->count()
        ];
    }

    public function getAssessmentProgress(AssessmentSession $session): array
    {
        $assessableResults = $this->getAssessableResults($session);
        $total = $assessableResults->count();
        $completed = $assessableResults->where('status', 'completed')->count();

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
