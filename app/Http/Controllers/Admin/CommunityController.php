<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityTemplate;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $templates = CommunityTemplate::with('user')
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('author_name', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.community.index', compact('templates', 'search'));
    }

    public function destroy(CommunityTemplate $template)
    {
        $title = $template->title;
        $template->delete();

        return back()->with('success', "Template \"{$title}\" deleted successfully.");
    }
}
