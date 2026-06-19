<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityTemplate;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->input('search');
        $sortBy  = $request->input('sort', 'newest');

        // Stats
        $totalTemplates = CommunityTemplate::count();
        $totalDownloads = CommunityTemplate::sum('downloads_count');
        $totalUpvotes   = CommunityTemplate::sum('upvotes');

        $query = CommunityTemplate::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });

        // Sorting
        match ($sortBy) {
            'downloads' => $query->orderByDesc('downloads_count'),
            'upvotes'   => $query->orderByDesc('upvotes'),
            'rating'    => $query->where('rating_count', '>', 0)
                                 ->orderByRaw('(rating_sum / rating_count) DESC'),
            'score'     => $query->orderByDesc('base_score'),
            default     => $query->orderByDesc('created_at'),
        };

        $templates = $query->paginate(15)->withQueryString();

        return view('admin.community.index', compact(
            'templates', 'search', 'sortBy',
            'totalTemplates', 'totalDownloads', 'totalUpvotes'
        ));
    }

    public function destroy(CommunityTemplate $template)
    {
        $title = $template->title;
        $template->delete();

        return back()->with('success', "Template \"{$title}\" deleted successfully.");
    }
}
