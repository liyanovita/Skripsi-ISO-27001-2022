@extends('layouts.admin')

@section('title', 'Knowledge Base Management')
@section('header_title', 'Knowledge Base')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">Knowledge Base</h2>
        <p class="text-sm text-slate-500">Manage guidance articles and document templates.</p>
    </div>
    <a href="{{ route('admin.knowledge.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">
        <i class="fa-solid fa-plus"></i> Add Article / Document
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50">
        <form method="GET" action="{{ route('admin.knowledge.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or content..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <select name="category" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white min-w-[150px]">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('admin.knowledge.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors flex items-center justify-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Title & Details</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4">Attachment</th>
                    <th class="px-6 py-4 text-center">Downloads</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($knowledgeBases as $kb)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                                <i class="{{ $kb->icon ?? 'fa-solid fa-file-lines' }}"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800">{{ $kb->title }}</div>
                                <div class="text-xs text-slate-500 line-clamp-1 mt-0.5" title="{{ $kb->description }}">{{ $kb->description }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600">
                            {{ $kb->category }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($kb->attachment_path)
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded text-xs font-bold uppercase">{{ $kb->format }}</span>
                                <span class="text-xs text-slate-400">{{ $kb->size }}</span>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">No attachment</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="font-bold text-slate-700">{{ number_format($kb->downloads_count) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2" x-data="{ showDelete: false }">
                            <a href="{{ route('admin.knowledge.edit', $kb) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            
                            <button type="button" @click="showDelete = true" x-show="!showDelete" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>

                            <form method="POST" action="{{ route('admin.knowledge.destroy', $kb) }}" x-show="showDelete" x-cloak class="flex items-center gap-1">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-2 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">Yes</button>
                                <button type="button" @click="showDelete = false" class="px-2 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded hover:bg-slate-300">No</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <i class="fa-solid fa-folder-open text-4xl mb-4 text-slate-300"></i>
                        <p>No knowledge base items found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($knowledgeBases->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $knowledgeBases->links() }}
    </div>
    @endif
</div>
@endsection
