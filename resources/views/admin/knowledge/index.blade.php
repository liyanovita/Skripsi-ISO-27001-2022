@extends('layouts.admin')

@section('title', 'Knowledge Base Management')
@section('header_title', 'Knowledge Base')

@section('content')
<div x-data="{ previewItem: null, showPreview: false }">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800">Knowledge Base</h2>
            <p class="text-sm text-slate-500">Manage guidance articles, policy documents, and templates.</p>
        </div>
        <a href="{{ route('admin.knowledge.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Add Article / Document
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">{{ number_format($totalCount) }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Articles</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">{{ number_format($systemCount) }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">System (Official)</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">{{ number_format($customCount) }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">User-Created</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-download"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">{{ number_format($totalDownloads) }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Downloads</div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

        {{-- Filter Bar --}}
        <div class="p-4 border-b border-slate-200 bg-slate-50">
            <form method="GET" action="{{ route('admin.knowledge.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, description or content..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <select name="category" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white min-w-[140px]">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                <select name="source" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white min-w-[130px]">
                    <option value="">All Sources</option>
                    <option value="system" {{ request('source') == 'system' ? 'selected' : '' }}>System Only</option>
                    <option value="custom" {{ request('source') == 'custom' ? 'selected' : '' }}>Custom Only</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'category', 'source']))
                    <a href="{{ route('admin.knowledge.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors flex items-center justify-center gap-1">
                        <i class="fa-solid fa-xmark"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Title & Details</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Source</th>
                        <th class="px-6 py-4">Attachment</th>
                        <th class="px-6 py-4 text-center">Downloads</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($knowledgeBases as $kb)
                    @php
                        $item = [
                            'title'       => $kb->title,
                            'category'    => $kb->category,
                            'description' => $kb->description,
                            'content'     => $kb->content,
                            'icon'        => $kb->icon ?? 'fa-solid fa-file-lines',
                            'is_system'   => $kb->is_system,
                            'format'      => $kb->format,
                            'size'        => $kb->size,
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                                    <i class="{{ $kb->icon ?? 'fa-solid fa-file-lines' }}"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 max-w-xs truncate">{{ $kb->title }}</div>
                                    <div class="text-xs text-slate-400 line-clamp-1 mt-0.5 max-w-xs" title="{{ $kb->description }}">{{ $kb->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600">
                                {{ $kb->category }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($kb->is_system)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-indigo-100 text-indigo-700">
                                    <i class="fa-solid fa-shield-halved text-[8px]"></i> System
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-emerald-100 text-emerald-700">
                                    <i class="fa-solid fa-user text-[8px]"></i> Custom
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($kb->attachment_path)
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded text-xs font-bold uppercase">{{ $kb->format }}</span>
                                    <span class="text-xs text-slate-400">{{ $kb->size }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">No attachment</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-bold text-slate-700">{{ number_format($kb->downloads_count) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1.5" x-data="{ showDelete: false }">
                                {{-- Preview --}}
                                <button type="button"
                                    @click="previewItem = @js($item); showPreview = true"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors"
                                    title="Preview">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                                {{-- Edit --}}
                                <a href="{{ route('admin.knowledge.edit', $kb) }}"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-50 transition-colors"
                                    title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                {{-- Delete --}}
                                <button type="button" @click="showDelete = true" x-show="!showDelete"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors"
                                    title="Delete">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.knowledge.destroy', $kb) }}" x-show="showDelete" x-cloak class="flex items-center gap-1">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">Delete</button>
                                    <button type="button" @click="showDelete = false" class="px-2 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded hover:bg-slate-300">Cancel</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <i class="fa-solid fa-folder-open text-4xl mb-4 text-slate-300 block"></i>
                            <p class="text-slate-500 font-medium">No knowledge base items found.</p>
                            @if(request()->hasAny(['search', 'category', 'source']))
                                <p class="text-sm text-slate-400 mt-1">Try clearing your filters.</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($knowledgeBases->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $knowledgeBases->links() }}
        </div>
        @endif
    </div>

    {{-- Preview Modal --}}
    <div x-show="showPreview" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        @keydown.escape.window="showPreview = false">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-2xl w-full max-h-[85vh] flex flex-col"
            @click.away="showPreview = false">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-5 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i :class="previewItem?.icon ?? 'fa-solid fa-file-lines'"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 text-sm" x-text="previewItem?.title"></h3>
                        <span class="text-[10px] font-bold uppercase tracking-widest"
                            :class="previewItem?.is_system ? 'text-indigo-600' : 'text-emerald-600'"
                            x-text="previewItem?.is_system ? '🛡 System (Official)' : '✏️ User-Created'"></span>
                    </div>
                </div>
                <button @click="showPreview = false" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            {{-- Modal Meta --}}
            <div class="px-5 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-3 shrink-0">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Category:</span>
                <span class="text-xs font-bold text-slate-700 bg-slate-200 px-2 py-0.5 rounded" x-text="previewItem?.category"></span>
                <span class="text-[10px] text-slate-400 ml-auto italic" x-text="previewItem?.description"></span>
            </div>
            {{-- Modal Body --}}
            <div class="p-5 overflow-y-auto flex-1
                [&::-webkit-scrollbar]:w-1.5
                [&::-webkit-scrollbar-track]:bg-slate-100
                [&::-webkit-scrollbar-track]:rounded-full
                [&::-webkit-scrollbar-thumb]:bg-slate-300
                [&::-webkit-scrollbar-thumb]:rounded-full">
                <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed text-xs" x-html="previewItem?.content ? previewItem.content.replace(/\n/g,'<br>') : '<em>No content</em>'"></div>
            </div>
        </div>
    </div>

</div>
@endsection
