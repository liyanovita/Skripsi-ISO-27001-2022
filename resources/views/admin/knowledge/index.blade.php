@extends('layouts.admin')

@section('title', 'Knowledge Base Management')
@section('header_title', 'Knowledge Base')

@section('content')
<div>

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
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
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
            <form method="GET" action="{{ route('admin.knowledge.index') }}" x-data class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        x-on:input.debounce.500ms="$el.closest('form').requestSubmit()"
                        placeholder="Search title, description or content..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <select name="category" x-on:change="$el.closest('form').requestSubmit()" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white min-w-[140px]">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                            {{ $cat === 'sop' ? 'SOP' : __(ucfirst($cat)) }}
                        </option>
                    @endforeach
                </select>
                @if(request()->hasAny(['search', 'category']))
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
                                {{ $kb->category === 'sop' ? 'SOP' : __(ucfirst($kb->category)) }}
                            </span>
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
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Preview --}}
                                <a href="{{ route('admin.knowledge.show', $kb) }}"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors"
                                    title="Preview">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                {{-- Edit --}}
                                <a href="{{ route('admin.knowledge.edit', $kb) }}"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-50 transition-colors"
                                    title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                {{-- Delete --}}
                                <form method="POST" action="{{ route('admin.knowledge.destroy', $kb) }}"
                                    x-data
                                    @submit.prevent="
                                        Swal.fire({
                                            title: '{{ addslashes(__('Delete Document?')) }}',
                                            text: '{{ addslashes(__('Are you sure you want to delete document ":title"? This action cannot be undone.', ['title' => $kb->title])) }}',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#64748b',
                                            confirmButtonText: '{{ addslashes(__('Yes, Delete!')) }}',
                                            cancelButtonText: '{{ addslashes(__('Cancel')) }}',
                                            width: '22rem',
                                            customClass: {
                                                title: 'text-base font-bold text-slate-800',
                                                htmlContainer: 'text-xs text-slate-500',
                                                confirmButton: 'text-xs px-3 py-2 rounded-lg font-semibold',
                                                cancelButton: 'text-xs px-3 py-2 rounded-lg font-semibold'
                                            }
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $el.submit();
                                            }
                                        });
                                    ">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors"
                                        title="Delete">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
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


</div>
@endsection
