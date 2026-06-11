@extends('layouts.admin')

@section('title', 'Community Moderation')
@section('header_title', 'Community Templates')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    {{-- Header --}}
    <div class="p-5 border-b border-slate-200">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="font-bold text-slate-800">All Community Templates</h3>
                <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-full">{{ $templates->total() }} total</span>
            </div>
            <form method="GET" action="{{ route('admin.community.index') }}" class="w-full sm:w-72 relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search title or author..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Template</th>
                    <th class="px-6 py-4">Author</th>
                    <th class="px-6 py-4">Base Score</th>
                    <th class="px-6 py-4">Rating</th>
                    <th class="px-6 py-4">Downloads</th>
                    <th class="px-6 py-4">Upvotes</th>
                    <th class="px-6 py-4">Created</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($templates as $template)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-900">{{ $template->title }}</div>
                        <div class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $template->description }}</div>
                        @if($template->tags && count($template->tags) > 0)
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            @foreach(array_slice($template->tags, 0, 3) as $tag)
                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-medium">{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800 text-xs">{{ $template->author_name }}</div>
                        @if($template->user)
                        <a href="{{ route('admin.users.show', $template->user_id) }}" class="text-[10px] text-blue-600 hover:underline">{{ $template->user->email }}</a>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold {{ $template->base_score >= 4 ? 'text-emerald-600' : ($template->base_score >= 2.5 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ number_format($template->base_score, 1) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($template->rating_count > 0)
                        <div class="flex items-center gap-1">
                            <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                            <span class="font-bold text-slate-700">{{ $template->avg_rating }}</span>
                            <span class="text-[10px] text-slate-400">({{ $template->rating_count }})</span>
                        </div>
                        @else
                        <span class="text-xs text-slate-400 italic">No ratings</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-slate-700">{{ $template->downloads_count }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-slate-700 flex items-center gap-1">
                            <i class="fa-solid fa-arrow-up text-emerald-500 text-[10px]"></i>
                            {{ $template->upvotes }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-500">
                        {{ $template->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2" x-data="{ showDelete: false }">
                            <button @click="showDelete = true" x-show="!showDelete" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-600 hover:bg-red-50 bg-white border border-red-200 transition-colors" title="Delete">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.community.destroy', $template) }}" x-show="showDelete" class="flex gap-1" x-cloak>
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">Confirm</button>
                                <button type="button" @click="showDelete = false" class="px-3 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded hover:bg-slate-300">Cancel</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                        <i class="fa-solid fa-file-circle-xmark text-3xl mb-3 text-slate-300"></i>
                        <p>No community templates found.</p>
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
