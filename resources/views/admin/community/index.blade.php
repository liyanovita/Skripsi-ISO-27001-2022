@extends('layouts.admin')

@section('title', 'Community Moderation')
@section('header_title', 'Community Templates')

@section('content')

{{-- Page Header --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">Community Moderation</h2>
        <p class="text-sm text-slate-500">Review, inspect, and moderate user-submitted audit templates.</p>
    </div>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-lg shrink-0">
            <i class="fa-solid fa-file-contract"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Total Templates</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($totalTemplates) }}</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-lg shrink-0">
            <i class="fa-solid fa-download"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Total Downloads</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($totalDownloads) }}</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-lg shrink-0">
            <i class="fa-solid fa-thumbs-up"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Total Upvotes</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($totalUpvotes) }}</span>
        </div>
    </div>
</div>

{{-- Table Card --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    {{-- Toolbar: Search + Sort --}}
    <div class="p-5 border-b border-slate-200 bg-slate-50">
        <form method="GET" action="{{ route('admin.community.index') }}" id="filter-form" x-data
              class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 flex-wrap">

            {{-- Left: Total + Search --}}
            <div class="flex items-center gap-3 flex-1 flex-wrap">
                <span class="text-xs font-bold text-slate-400 bg-white border border-slate-200 px-2.5 py-1 rounded-full shrink-0">
                    {{ $templates->total() }} templates
                </span>
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" id="search-input" value="{{ $search }}"
                        x-on:input.debounce.500ms="$el.closest('form').requestSubmit()"
                        placeholder="Search title, author..."
                        class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white">
                </div>
            </div>

            {{-- Right: Sort + Clear --}}
            <div class="flex items-center gap-2 shrink-0">
                <select name="sort" onchange="document.getElementById('filter-form').requestSubmit()"
                    class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white font-semibold text-slate-700">
                    <option value="newest"    {{ $sortBy == 'newest'    ? 'selected' : '' }}>Newest</option>
                    <option value="downloads" {{ $sortBy == 'downloads' ? 'selected' : '' }}>Most Downloaded</option>
                    <option value="upvotes"   {{ $sortBy == 'upvotes'   ? 'selected' : '' }}>Most Upvoted</option>
                    <option value="rating"    {{ $sortBy == 'rating'    ? 'selected' : '' }}>Highest Rated</option>
                    <option value="score"     {{ $sortBy == 'score'     ? 'selected' : '' }}>Highest Score</option>
                </select>
                @if($search || $sortBy !== 'newest')
                <a href="{{ route('admin.community.index') }}"
                    class="px-3 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-xs"></i> Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-4">Template</th>
                    <th class="px-5 py-4">Author</th>
                    <th class="px-5 py-4 text-center">Base Score</th>
                    <th class="px-5 py-4 text-center">Rating</th>
                    <th class="px-5 py-4 text-center">Downloads</th>
                    <th class="px-5 py-4 text-center">Upvotes</th>
                    <th class="px-5 py-4">Created</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($templates as $template)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-5 py-4 max-w-[220px]">
                        <div class="font-bold text-slate-900 truncate">{{ $template->title }}</div>
                        <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $template->description }}</div>
                        @if($template->tags && count($template->tags) > 0)
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            @foreach(array_slice($template->tags, 0, 3) as $tag)
                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[9px] font-semibold">{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="font-semibold text-slate-800 text-xs">{{ $template->author_name }}</div>
                        @if($template->user)
                        <a href="{{ route('admin.users.show', $template->user_id) }}"
                            class="text-[10px] text-blue-500 hover:underline">
                            {{ $template->user->email }}
                        </a>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-black text-sm
                            {{ $template->base_score >= 4 ? 'text-emerald-600' : ($template->base_score >= 2.5 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ number_format($template->base_score, 1) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        @if($template->rating_count > 0)
                        <div class="inline-flex items-center gap-1">
                            <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                            <span class="font-bold text-slate-700 text-xs">{{ $template->avg_rating }}</span>
                            <span class="text-[10px] text-slate-400">({{ $template->rating_count }})</span>
                        </div>
                        @else
                        <span class="text-xs text-slate-300 italic">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center gap-1 font-bold text-slate-700 text-xs">
                            <i class="fa-solid fa-download text-slate-400 text-[9px]"></i>
                            {{ $template->downloads_count }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-700 text-xs">
                            <i class="fa-solid fa-arrow-up text-emerald-500 text-[9px]"></i>
                            {{ $template->upvotes }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">
                        {{ $template->created_at->format('d M Y') }}
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Preview --}}
                            <a href="{{ route('community.preview', $template->id) }}"
                                target="_blank"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-indigo-600 hover:bg-indigo-50 border border-indigo-200 bg-white transition-colors"
                                title="Preview Template">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.community.destroy', $template) }}"
                                x-data
                                @submit.prevent="
                                    Swal.fire({
                                        title: '{{ addslashes(__('Delete Template?')) }}',
                                        text: '{{ addslashes(__('Are you sure you want to delete template ":title"? This action cannot be undone.', ['title' => $template->title])) }}',
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
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 border border-red-200 bg-white transition-colors"
                                    title="Delete Template">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center">
                        <i class="fa-solid fa-file-circle-xmark text-4xl text-slate-200 mb-3 block"></i>
                        <p class="text-slate-500 font-semibold">No community templates found.</p>
                        <p class="text-slate-400 text-xs mt-1">Templates submitted by users will appear here.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($templates->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $templates->links() }}
    </div>
    @endif
</div>
@endsection
