<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\KnowledgeBase::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('source')) {
            if ($request->source === 'system') {
                $query->where('is_system', true);
            } elseif ($request->source === 'custom') {
                $query->where('is_system', false);
            }
        }

        $knowledgeBases = $query->latest()->paginate(10)->withQueryString();
        $categories     = \App\Models\KnowledgeBase::select('category')->distinct()->pluck('category');

        // Stats for header cards
        $totalCount   = \App\Models\KnowledgeBase::count();
        $systemCount  = \App\Models\KnowledgeBase::where('is_system', true)->count();
        $customCount  = \App\Models\KnowledgeBase::where('is_system', false)->count();
        $totalDownloads = \App\Models\KnowledgeBase::sum('downloads_count');

        return view('admin.knowledge.index', compact(
            'knowledgeBases', 'categories',
            'totalCount', 'systemCount', 'customCount', 'totalDownloads'
        ));
    }

    public function create()
    {
        return view('admin.knowledge.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'content' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', // max 10MB
        ]);

        $kb = new \App\Models\KnowledgeBase();
        $kb->title = $validated['title'];
        $kb->category = $validated['category'];
        $kb->description = $validated['description'];
        $kb->content = $validated['content'];
        $kb->icon = $validated['icon'] ?? 'fa-solid fa-file-lines';
        $kb->is_system = true; // Admin created

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('knowledge-base', 'public');
            
            $kb->attachment_path = $path;
            $kb->attachment_name = $file->getClientOriginalName();
            $kb->attachment_mime = $file->getClientMimeType();
            $kb->attachment_size = $file->getSize();
            $kb->format = $file->getClientOriginalExtension();
            $kb->size = round($file->getSize() / 1024 / 1024, 2) . ' MB';
        } else {
            $kb->format = 'article';
            $kb->size = '0 MB';
        }

        $kb->save();

        return redirect()->route('admin.knowledge.index')->with('success', 'Knowledge Base article created successfully.');
    }

    public function edit(\App\Models\KnowledgeBase $knowledge)
    {
        return view('admin.knowledge.edit', compact('knowledge'));
    }

    public function update(Request $request, \App\Models\KnowledgeBase $knowledge)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'content' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
        ]);

        $knowledge->title = $validated['title'];
        $knowledge->category = $validated['category'];
        $knowledge->description = $validated['description'];
        $knowledge->content = $validated['content'];
        $knowledge->icon = $validated['icon'] ?? $knowledge->icon;

        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($knowledge->attachment_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($knowledge->attachment_path);
            }

            $file = $request->file('attachment');
            $path = $file->store('knowledge-base', 'public');
            
            $knowledge->attachment_path = $path;
            $knowledge->attachment_name = $file->getClientOriginalName();
            $knowledge->attachment_mime = $file->getClientMimeType();
            $knowledge->attachment_size = $file->getSize();
            $knowledge->format = $file->getClientOriginalExtension();
            $knowledge->size = round($file->getSize() / 1024 / 1024, 2) . ' MB';
        }

        $knowledge->save();

        return redirect()->route('admin.knowledge.index')->with('success', 'Knowledge Base article updated successfully.');
    }

    public function destroy(\App\Models\KnowledgeBase $knowledge)
    {
        if ($knowledge->attachment_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($knowledge->attachment_path);
        }

        $knowledge->delete();

        return redirect()->route('admin.knowledge.index')->with('success', 'Knowledge Base article deleted successfully.');
    }
}
