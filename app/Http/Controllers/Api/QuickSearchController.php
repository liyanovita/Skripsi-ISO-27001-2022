<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\KnowledgeBase;
use App\Models\CommunityTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuickSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q       = trim($request->get('q', ''));
        $userId  = auth()->id();
        $results = [];

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        // 1. Audit Sessions
        AssessmentSession::forUser($userId)
            ->where('name', 'like', "%{$q}%")
            ->limit(3)
            ->get()
            ->each(function ($s) use (&$results) {
                $results[] = [
                    'type'     => 'session',
                    'title'    => $s->name,
                    'subtitle' => ucfirst($s->status) . ' · ' . $s->created_at->format('M Y'),
                    'url'      => route('sessions.show', $s->id),
                ];
            });

        // 2. Gap Controls (by standard code or title)
        AssessmentResult::with('standard', 'session')
            ->whereHas('session', fn($q2) => $q2->where('user_id', $userId))
            ->whereHas('standard', fn($q2) => $q2->where('code', 'like', "%{$q}%")
                ->orWhere('title', 'like', "%{$q}%"))
            ->where('maturity_rating', '<', 4)
            ->where('maturity_rating', '>=', 0)
            ->where('status', 'completed')
            ->limit(4)
            ->get()
            ->each(function ($r) use (&$results) {
                $results[] = [
                    'type'     => 'gap',
                    'title'    => $r->standard->code . ' — ' . $r->standard->title,
                    'subtitle' => 'Maturity ' . $r->maturity_rating . '/5 · ' . ($r->session->name ?? ''),
                    'url'      => route('workspace.index', ['session_id' => $r->session_id, 'tab' => 'gap-report']),
                ];
            });

        // 3. Knowledge Base
        KnowledgeBase::where('title', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->limit(3)
            ->get()
            ->each(function ($kb) use (&$results) {
                $results[] = [
                    'type'     => 'kb',
                    'title'    => $kb->title,
                    'subtitle' => ucfirst($kb->category) . ' · ' . strtoupper($kb->format),
                    'url'      => route('knowledge-base.index'),
                ];
            });

        // 4. Community Templates
        CommunityTemplate::where('title', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->limit(3)
            ->get()
            ->each(function ($t) use (&$results) {
                $results[] = [
                    'type'     => 'community',
                    'title'    => $t->title,
                    'subtitle' => 'by ' . ($t->author_name ?? 'Anonymous') . ' · ★ ' . number_format($t->avg_rating, 1),
                    'url'      => route('community.preview', $t->id),
                ];
            });

        return response()->json(['results' => array_slice($results, 0, 10)]);
    }
}
