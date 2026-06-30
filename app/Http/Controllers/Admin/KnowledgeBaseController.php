<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\KnowledgeBase::where('is_system', true);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        $knowledgeBases = $query->latest()->paginate(10)->withQueryString();
        $categories     = ['guides', 'templates', 'sop', 'evidence'];

        // Stats for header cards
        $totalCount   = \App\Models\KnowledgeBase::where('is_system', true)->count();
        $totalDownloads = \App\Models\KnowledgeBase::where('is_system', true)->sum('downloads_count');

        return view('admin.knowledge.index', compact(
            'knowledgeBases', 'categories',
            'totalCount', 'totalDownloads'
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
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt,md,csv', // max 10MB
        ]);

        $kb = new \App\Models\KnowledgeBase();
        $kb->title = $validated['title'];
        $kb->category = $validated['category'];
        $kb->description = $validated['description'];
        $kb->content = $validated['content'];
        $kb->icon = match ($validated['category']) {
            'guides' => 'fa-solid fa-route',
            'templates' => 'fa-solid fa-file-lines',
            'sop' => 'fa-solid fa-list-check',
            default => 'fa-solid fa-file-shield',
        };
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
        if (!$knowledge->is_system) {
            abort(404);
        }
        return view('admin.knowledge.edit', compact('knowledge'));
    }

    public function update(Request $request, \App\Models\KnowledgeBase $knowledge)
    {
        if (!$knowledge->is_system) {
            abort(404);
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt,md,csv',
        ]);

        $knowledge->title = $validated['title'];
        $knowledge->category = $validated['category'];
        $knowledge->description = $validated['description'];
        $knowledge->content = $validated['content'];
        $knowledge->icon = match ($validated['category']) {
            'guides' => 'fa-solid fa-route',
            'templates' => 'fa-solid fa-file-lines',
            'sop' => 'fa-solid fa-list-check',
            default => 'fa-solid fa-file-shield',
        };

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
        if (!$knowledge->is_system) {
            abort(404);
        }
        if ($knowledge->attachment_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($knowledge->attachment_path);
        }

        $knowledge->delete();

        return redirect()->route('admin.knowledge.index')->with('success', 'Knowledge Base article deleted successfully.');
    }

    public function show(\App\Models\KnowledgeBase $knowledge)
    {
        if (!$knowledge->is_system) {
            abort(404);
        }

        if ($knowledge->isHtml()) {
            $contentHtml = $knowledge->content;
        } else {
            $contentHtml = (string) \Illuminate\Support\Str::markdown(e($knowledge->content));
        }

        // Format LaTeX formulas into beautiful inline styled widgets
        $pattern = '/\\$\\$\\s*\\\\text\\{\\s*Risk\\s+Score\\s*\\}\\s*=\\s*\\\\text\\{\\s*Impact\\s*\\}\\s*\\\\times\\s*\\\\text\\{\\s*Likelihood\\s*\\}\\s*\\$\\$/i';
        $replacement = '<span class="inline-flex items-center px-2 py-0.5 bg-slate-50 border border-slate-200/60 rounded-lg font-serif text-[11px] font-semibold text-slate-800 mx-1"><span class="font-bold text-indigo-600">Risk Score</span>&nbsp;=&nbsp;<span class="font-medium text-slate-700">Impact</span>&nbsp;&times;&nbsp;<span class="font-medium text-slate-700">Likelihood</span></span>';
        $contentHtml = preg_replace($pattern, $replacement, $contentHtml);

        return view('admin.knowledge.show', [
            'resource' => $knowledge,
            'contentHtml' => $contentHtml
        ]);
    }
}
