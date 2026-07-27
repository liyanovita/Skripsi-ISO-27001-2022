@extends('layouts.app')
@section('title', 'Knowledge Base')
@section('view_name', 'Knowledge Base')

@section('content')
<div class="w-full space-y-5 pb-8" x-data="{ 
    resources: @js($resources->getCollection()->map(fn($r) => [
        'id' => $r->id,
        'title' => $r->title,
        'category' => $r->category,
        'category_label' => $r->category === 'sop' ? 'SOP' : __(ucfirst($r->category)),
        'category_class' => $r->category === 'guides' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : ($r->category === 'templates' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : ($r->category === 'sop' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-blue-50 text-blue-700 border-blue-100')),
        'type' => $r->format ? strtoupper($r->format) : 'PDF',
        'desc' => collect(preg_split('/(?<=[.?!])\s+(?=[A-Za-z])/', $r->description ?? ''))->take(1)->implode(' '),
        'content' => $r->content,
        'content_html' => (string) Str::markdown(e($r->content)),
        'size' => $r->size ?: ($r->is_system ? round(strlen($r->content) / 1024 + 12) . 'KB' : ''),
        'has_attachment' => filled($r->attachment_path),
        'attachment_name' => $r->attachment_name ?? '',
        'attachment_url' => filled($r->attachment_path) ? route('knowledge-base.attachment', $r->id) : null,
        'downloads' => $r->downloads_count,
        'updated_at' => $r->updated_at?->format('d M Y') ?? '',
        'is_system' => $r->is_system,
        'edit_url' => route('knowledge-base.edit', $r->id),
        'delete_url' => route('knowledge-base.destroy', $r->id),
        'download_url' => route('knowledge-base.download', $r->id),
        'show_url' => route('knowledge-base.show', $r->id)
    ]))
}">

    {{-- Page Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-book-open text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-800 tracking-tight">{{ __('Knowledge Base') }}</h1>
                <p class="text-slate-400 font-semibold text-xs mt-0.5">{{ __('ISO 27001:2022 Implementation Guides, Policy Templates & SOPs') }}</p>
            </div>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('knowledge-base.export-json') }}" data-turbo="false"
                class="px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-file-export text-slate-400"></i> {{ __('Export JSON') }}
            </a>
            <form action="{{ route('knowledge-base.import-json') }}" method="POST" enctype="multipart/form-data" class="inline">
                @csrf
                <label class="px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-file-import text-blue-500"></i> {{ __('Import') }}
                    <input type="file" name="json_file" accept="application/json,.json" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <a href="{{ route('knowledge-base.create') }}" id="btn-create-article"
                class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-all shadow-md shadow-indigo-600/20 active:scale-95 flex items-center gap-1.5">
                <i class="fa-solid fa-plus text-[10px]"></i> {{ __('Add Resource') }}
            </a>
        </div>
        @endif
    </div>

    {{-- Search and Filter Bar --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
        <form action="{{ route('knowledge-base.index') }}" method="GET" class="space-y-4" x-data x-on:change="$el.requestSubmit()">
            <div class="flex flex-col lg:flex-row gap-3">
                <div class="flex-1 relative group">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-600 transition-colors text-sm"></i>
                    <input 
                        type="text" 
                        name="q"
                        id="kb-search-bar"
                        value="{{ $search ?? '' }}"
                        x-on:input.debounce.500ms="$el.closest('form').requestSubmit()"
                        placeholder="{{ __('Search knowledge assets, templates, or compliance guides...') }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 {{ !empty($search) ? 'pr-10' : 'pr-4' }} py-2.5 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/5 transition-all"
                    >
                    @if(!empty($search))
                        <a href="{{ route('knowledge-base.index', array_merge(request()->except(['q', 'page']))) }}" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fa-solid fa-circle-xmark text-sm"></i>
                        </a>
                    @endif
                </div>

                <div class="relative min-w-48">
                    <select name="category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 transition-all cursor-pointer">
                        <option value="all" {{ ($selectedCategory ?? 'all') === 'all' ? 'selected' : '' }}>{{ __('All Categories') }}</option>
                        @foreach($categoryCounts as $category => $count)
                            <option value="{{ $category }}" {{ ($selectedCategory ?? 'all') === $category ? 'selected' : '' }}>{{ $category === 'sop' ? 'SOP' : __(ucfirst($category)) }} ({{ $count }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative min-w-48">
                    <select name="sort" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 transition-all cursor-pointer">
                        <option value="latest" {{ ($selectedSort ?? 'latest') === 'latest' ? 'selected' : '' }}>{{ __('Latest Updated') }}</option>
                        <option value="title" {{ ($selectedSort ?? 'latest') === 'title' ? 'selected' : '' }}>{{ __('Title A-Z') }}</option>
                        <option value="most_downloaded" {{ ($selectedSort ?? 'latest') === 'most_downloaded' ? 'selected' : '' }}>{{ __('Most Downloaded') }}</option>
                    </select>
                </div>
            </div>

            @if(($search ?? '') !== '' || ($selectedCategory ?? 'all') !== 'all' || ($selectedSort ?? 'latest') !== 'latest')
                <div class="flex items-center justify-between gap-3 rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-2 text-[11px] font-bold text-indigo-700">
                    <span>{{ __('Showing') }} {{ $filteredCount }} {{ __('of') }} {{ $totalCount }} {{ __('resources') }}</span>
                    @if(($search ?? '') !== '')
                        <span>{{ __('Search') }}: "{{ $search }}"</span>
                    @endif
                    <a href="{{ route('knowledge-base.index') }}" class="text-indigo-600 underline hover:text-indigo-800 font-bold">Clear Filters</a>
                </div>
            @endif
        </form>
    </div>

    {{-- Resources Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="res in resources" :key="res.id">
            <div @click="typeof Turbo !== 'undefined' ? Turbo.visit(res.show_url) : window.location.href = res.show_url"
                 class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md hover:border-slate-200 transition-all group flex flex-col justify-between cursor-pointer">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border" :class="res.category_class" x-text="res.category_label"></span>
                        <span class="px-2 py-0.5 bg-slate-900 text-white rounded text-[10px] font-mono font-bold uppercase shrink-0" x-text="res.type"></span>
                    </div>

                    <h3 class="text-sm font-black text-slate-800 tracking-tight group-hover:text-indigo-600 transition-colors leading-snug mb-1.5" x-text="res.title"></h3>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed line-clamp-2" x-text="res.desc"></p>

                    <template x-if="res.has_attachment">
                        <div class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                            <i class="fa-solid fa-paperclip"></i>
                            <span>{{ __('File Attached') }}</span>
                        </div>
                    </template>
                </div>

                <div class="pt-3 border-t border-slate-50 mt-4 flex items-center justify-between gap-2 text-[10px] font-bold text-slate-400">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1"><i class="fa-solid fa-download text-slate-300"></i> <span x-text="res.downloads"></span></span>
                        <template x-if="res.size">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-database text-slate-300"></i> <span x-text="res.size"></span></span>
                        </template>
                    </div>

                    <div class="flex items-center gap-1">
                        <template x-if="res.is_system">
                            <a :href="res.download_url" data-turbo="false" @click.stop class="w-7 h-7 bg-slate-100 text-slate-600 rounded-lg flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-colors" title="{{ __('Download PDF') }}">
                                <i class="fa-solid fa-download text-xs"></i>
                            </a>
                        </template>

                        <template x-if="res.has_attachment">
                            <a :href="res.attachment_url" data-turbo="false" @click.stop class="w-7 h-7 bg-emerald-50 text-emerald-700 rounded-lg flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-colors" title="{{ __('Download Attachment') }}">
                                <i class="fa-solid fa-paperclip text-xs"></i>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty State --}}
    <div x-show="resources.length === 0" x-transition class="py-16 text-center bg-white rounded-2xl border border-slate-100">
        <i class="fa-solid fa-folder-open text-4xl text-slate-200 mb-3 block"></i>
        <h3 class="text-slate-800 font-bold text-sm">{{ __('No Resources Found') }}</h3>
        <p class="text-xs text-slate-400 mt-1">{{ __('Try adjusting your search query or category filters.') }}</p>
    </div>

    @if($resources->hasPages())
        <div class="pt-2">
            {{ $resources->links() }}
        </div>
    @endif

</div>
@endsection
