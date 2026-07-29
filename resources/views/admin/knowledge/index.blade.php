@extends('layouts.admin')

@section('title', 'Knowledge Base Management')
@section('header_title', 'Knowledge Base')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-book-open"></i>
                </span>
                Knowledge Base
            </h2>
            <p class="text-sm text-slate-500 mt-0.5 ml-9">Manage guidance articles, ISO 27001:2022 policy templates, SOPs, and compliance evidence.</p>
        </div>
        <a href="{{ route('admin.knowledge.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-md shadow-blue-600/20 shrink-0">
            <i class="fa-solid fa-plus text-xs"></i> Add Document
        </a>
    </div>

    {{-- KPI Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Articles</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-book-open text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalCount) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">System knowledge items</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Downloads</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-download text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-emerald-600 tracking-tight">{{ number_format($totalDownloads) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Cumulative downloads</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Categories</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-layer-group text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">4</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Guides, Templates, SOP, Evidence</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Standard</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-award text-xs"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-blue-600 tracking-tight">ISO 27001:2022</div>
            <div class="text-[10px] text-slate-400 mt-0.5">2022 Implementation Asset</div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- Filter Bar --}}
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white">
            <form method="GET" action="{{ route('admin.knowledge.index') }}" x-data class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="flex-1 relative w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        x-on:input.debounce.400ms="$el.closest('form').requestSubmit()"
                        placeholder="Search title, description or content…"
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-400 transition-all bg-white">
                </div>
                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                    <select name="category" x-on:change="$el.closest('form').requestSubmit()"
                        class="px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none bg-white font-semibold text-slate-600 cursor-pointer w-full sm:w-auto">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ $cat === 'sop' ? 'SOP' : __(ucfirst($cat)) }}
                            </option>
                        @endforeach
                    </select>
                    @if(request()->hasAny(['search', 'category']))
                        <a href="{{ route('admin.knowledge.index') }}"
                            class="px-3 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-sm font-bold hover:bg-slate-200 transition-colors flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-xmark text-xs"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Document & Details</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Category</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Attachment</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Downloads</th>
                        <th class="px-5 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($knowledgeBases as $kb)
                    @php
                        $catStyle = match($kb->category) {
                            'guides' => 'bg-blue-50 text-blue-700 border-blue-100',
                            'templates' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'sop' => 'bg-amber-50 text-amber-700 border-amber-100',
                            'evidence' => 'bg-blue-50 text-blue-700 border-blue-100',
                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                    @endphp
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-sm shrink-0">
                                    <i class="{{ $kb->icon ?? 'fa-solid fa-file-lines' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.knowledge.show', $kb) }}" class="font-bold text-slate-900 hover:text-blue-600 transition-colors block truncate max-w-sm">
                                        {{ $kb->title }}
                                    </a>
                                    <div class="text-[11px] text-slate-400 truncate max-w-sm mt-0.5" title="{{ $kb->description }}">
                                        {{ $kb->description }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $catStyle }}">
                                {{ $kb->category === 'sop' ? 'SOP' : __(ucfirst($kb->category)) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($kb->attachment_path)
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                                        {{ $kb->format ?? 'FILE' }}
                                    </span>
                                    @if($kb->size)
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $kb->size }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-300 italic">No attachment</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="font-black text-slate-700">{{ number_format($kb->downloads_count) }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.knowledge.show', $kb) }}"
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-blue-500 hover:bg-blue-50 border border-blue-100 bg-white transition-colors" title="View">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('admin.knowledge.edit', $kb) }}"
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-amber-600 hover:bg-amber-50 border border-amber-100 bg-white transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.knowledge.destroy', $kb) }}"
                                    x-data
                                    @submit.prevent="
                                        Swal.fire({
                                            title: 'Delete Document?',
                                            text: 'Are you sure you want to delete document &quot;{{ addslashes($kb->title) }}&quot;? This action cannot be undone.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#64748b',
                                            confirmButtonText: 'Yes, Delete!',
                                            cancelButtonText: 'Cancel',
                                            width: '22rem',
                                            customClass: {
                                                title: 'text-base font-bold text-slate-800',
                                                htmlContainer: 'text-xs text-slate-500',
                                                confirmButton: 'text-xs px-3 py-2 rounded-lg font-semibold',
                                                cancelButton: 'text-xs px-3 py-2 rounded-lg font-semibold'
                                            }
                                        }).then((result) => {
                                            if (result.isConfirmed) { $el.submit(); }
                                        });
                                    ">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-xl flex items-center justify-center text-red-400 hover:bg-red-50 border border-red-100 bg-white transition-colors" title="Delete">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-20 text-center">
                            <div class="w-16 h-16 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-book-open text-3xl text-slate-200"></i>
                            </div>
                            <p class="text-slate-500 font-bold text-sm">No Knowledge Base Items Found</p>
                            <p class="text-slate-400 text-xs mt-1">Try adjusting your search query or category filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($knowledgeBases->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $knowledgeBases->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
