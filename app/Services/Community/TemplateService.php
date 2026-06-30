<?php

namespace App\Services\Community;

use App\Models\CommunityTemplate;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TemplateService
{
    public function getTemplatesData(?string $search): array
    {
        $query = CommunityTemplate::with('user')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%")
                  ->orWhere('author_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $dbTemplates = $query->get();
        $templates = $dbTemplates->map(fn($t) => (object) [
            'id'           => $t->id,
            'name'         => $t->title,
            'author'       => $t->author_name ?? ($t->user->name ?? 'Anonymous'),
            'rating'       => $t->avg_rating,
            'rating_count' => $t->rating_count ?? 0,
            'upvotes'      => $t->upvotes ?? 0,
            'downloads'    => $t->downloads_count,
            'tags'         => $t->tags ?? [],
            'is_db'        => true,
        ]);

        $topContributors = User::has('communityTemplates')
            ->withCount('communityTemplates')
            ->orderBy('community_templates_count', 'desc')
            ->take(4)
            ->get()
            ->map(fn($u) => [
                'name'     => $u->name,
                'role'     => $u->community_templates_count > 5 ? 'Senior Assessor' : 'Framework Contributor',
                'count'    => $u->community_templates_count,
                'initials' => strtoupper(substr($u->name, 0, 2)),
            ]);

        $recentActivity = CommunityTemplate::latest()->take(4)->get();

        $totalRatingSum   = $dbTemplates->sum('rating_sum');
        $totalRatingCount = $dbTemplates->sum('rating_count');
        $dynamicAvgRating = $totalRatingCount > 0
            ? round($totalRatingSum / $totalRatingCount, 1)
            : 0.0;

        $stats = [
            'total_downloads'    => $dbTemplates->sum('downloads_count'),
            'total_contributors' => User::has('communityTemplates')->count(),
            'total_templates'    => $dbTemplates->count(),
            'avg_rating'         => $dynamicAvgRating,
        ];

        return compact('templates', 'topContributors', 'recentActivity', 'stats');
    }

    public function createTemplate(array $data, int $userId): CommunityTemplate
    {
        // Validate required fields
        if (empty($data['title']) || empty($data['content_data'])) {
            throw new \Exception('Title and content data are required.');
        }

        // Validate title length
        if (strlen($data['title']) < 5 || strlen($data['title']) > 255) {
            throw new \Exception('Title must be between 5 and 255 characters.');
        }

        // Validate description if provided
        if (isset($data['description'])) {
            if (strlen($data['description']) < 10 || strlen($data['description']) > 2000) {
                throw new \Exception('Description must be between 10 and 2000 characters.');
            }
        }

        // Validate content_data is array
        if (!is_array($data['content_data'])) {
            throw new \Exception('Content data must be a valid array.');
        }

        return CommunityTemplate::create([
            'user_id'      => $userId,
            'title'        => $data['title'],
            'description'  => $data['description'] ?? '',
            'author_name'  => $data['author_name'] ?? 'Anonymous',
            'tags'         => $data['tags'] ?? [],
            'base_score'   => $data['base_score'] ?? 0,
            'content_data' => $data['content_data'],
        ]);
    }

    public function useTemplate(int $templateId, int $userId): AssessmentSession
    {
        $template = CommunityTemplate::findOrFail($templateId);

        // Validate template data structure
        if (!isset($template->content_data)) {
            throw new \Exception('Template data is missing or invalid.');
        }

        return DB::transaction(function () use ($template, $userId) {
            $data        = is_array($template->content_data) ? $template->content_data : json_decode($template->content_data, true);
            $sessionData = $data['session'] ?? $data;

            // Validate session data
            if (!isset($sessionData['results']) || !is_array($sessionData['results'])) {
                throw new \Exception('Template format is invalid: missing results data.');
            }

            $session = AssessmentSession::create([
                'user_id'                => $userId,
                'name'                   => $template->title . ' (Imported) - ' . date('d M Y'),
                'status'                 => 'in_progress',
                'overall_maturity_score' => $sessionData['overall_maturity_score'] ?? 0,
            ]);

            foreach ($sessionData['results'] as $res) {
                // Support both iso_standard_id (direct) and iso_code (lookup)
                $standardId = $res['iso_standard_id'] ?? null;
                if (!$standardId && isset($res['iso_code'])) {
                    $standardId = IsoStandard::where('code', $res['iso_code'])->value('id');
                }
                if (!$standardId) continue;

                AssessmentResult::create([
                    'session_id'        => $session->id,
                    'iso_standard_id'   => $standardId,
                    'maturity_rating'   => $res['maturity_rating'] ?? 0,
                    'status'            => 'completed',
                    'notes'             => 'Imported from community: ' . $template->title,
                    'answers'           => $res['answers'] ?? [],
                    'ai_recommendation' => $res['ai_recommendation'] ?? '',
                ]);
            }

            $template->increment('downloads_count');

            return $session;
        });
    }

    public function cloneTemplate(int $templateId, int $userId): AssessmentSession
    {
        $template = CommunityTemplate::findOrFail($templateId);
        $data     = is_array($template->content_data) ? $template->content_data : json_decode($template->content_data, true);

        // Normalise: support both { results: [...] } and { session: { results: [...] } }
        $sessionData = $data['session'] ?? $data;

        if (!$sessionData || !isset($sessionData['results']) || !is_array($sessionData['results'])) {
            throw new \Exception('Template format is invalid: missing or malformed results data.');
        }

        return DB::transaction(function () use ($template, $sessionData, $userId) {
            $session = AssessmentSession::create([
                'user_id'                => $userId,
                'name'                   => 'Cloned: ' . $template->title,
                'status'                 => 'in_progress',
                'overall_maturity_score' => $sessionData['overall_maturity_score'] ?? 0,
            ]);

            foreach ($sessionData['results'] as $res) {
                // Support both iso_standard_id (direct) and iso_code (lookup)
                $standardId = $res['iso_standard_id'] ?? null;
                if (!$standardId && isset($res['iso_code'])) {
                    $standardId = IsoStandard::where('code', $res['iso_code'])->value('id');
                }
                if (!$standardId) continue;

                AssessmentResult::create([
                    'session_id'      => $session->id,
                    'iso_standard_id' => $standardId,
                    'answers'         => $res['answers'] ?? [],
                    'maturity_rating' => $res['maturity_rating'] ?? 0,
                    'status'          => $res['status'] ?? 'completed',
                    'notes'           => $res['notes'] ?? null,
                ]);
            }

            $template->increment('downloads_count');

            return $session;
        });
    }

    public function upvote(int $templateId): bool
    {
        $template = CommunityTemplate::findOrFail($templateId);
        
        // Validate template exists and is active
        if (!$template->id) {
            throw new \Exception('Template not found or is inactive.');
        }
        
        $template->increment('upvotes');
        return true;
    }

    public function rate(int $templateId, int $stars): bool
    {
        // Validate rating value
        if ($stars < 1 || $stars > 5) {
            throw new \Exception('Rating must be between 1 and 5 stars.');
        }

        $template = CommunityTemplate::findOrFail($templateId);
        
        // Validate template exists and is active
        if (!$template->id) {
            throw new \Exception('Template not found or is inactive.');
        }
        
        $template->increment('rating_sum', $stars);
        $template->increment('rating_count', 1);
        return true;
    }

    public function getTemplateWithResults(int $templateId): array
    {
        $template = CommunityTemplate::findOrFail($templateId);
        $data     = is_array($template->content_data) ? $template->content_data : json_decode($template->content_data, true);

        $results = [];
        $sessionData = $data['session'] ?? $data;
        
        if (isset($sessionData['results'])) {
            $standardIds = collect($sessionData['results'])->pluck('iso_standard_id');
            $standards   = IsoStandard::whereIn('id', $standardIds)->get()->keyBy('id');

            foreach ($sessionData['results'] as $res) {
                // Support both iso_standard_id (direct) and iso_code (lookup)
                $standardId = $res['iso_standard_id'] ?? null;
                if (!$standardId && isset($res['iso_code'])) {
                    $standardId = IsoStandard::where('code', $res['iso_code'])->value('id');
                }
                
                if ($standardId && isset($standards[$standardId])) {
                    $results[] = (object) [
                        'standard'        => $standards[$standardId],
                        'maturity_rating' => $res['maturity_rating'] ?? 0,
                        'notes'           => $res['notes'] ?? '',
                        'answers'         => $res['answers'] ?? [],
                    ];
                }
            }

            usort($results, fn($a, $b) => strcmp($a->standard->code, $b->standard->code));
        }

        return compact('template', 'results');
    }
}
