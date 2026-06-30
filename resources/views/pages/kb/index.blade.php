@extends('layouts.app')
@section('title', 'Knowledge Base')
@section('view_name', 'Knowledge Base')

@section('content')
<div class="w-full space-y-4 pb-8" x-data="{ 
    resources: @js($resources->getCollection()->map(fn($r) => [
        'id' => $r->id,
        'title' => $r->title,
        'category' => $r->category,
        'category_label' => $r->category === 'sop' ? 'SOP' : __(ucfirst($r->category)),
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

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-book-open text-lg"></i>
            </div>
            <div class="leading-none">
                <h1 class="text-xl font-black text-slate-900 tracking-tighter uppercase">{{ __('Knowledge Base') }}</h1>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-0.5">{{ __('Internal Documentation & Resources') }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <a href="{{ route('knowledge-base.export-json') }}" data-turbo="false" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-export"></i> {{ __('Export') }}
            </a>
            <form action="{{ route('knowledge-base.import-json') }}" method="POST" enctype="multipart/form-data" class="flex">
                @csrf
                <label class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-file-import"></i> {{ __('Import') }}
                    <input type="file" name="json_file" accept="application/json,.json" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <a href="{{ route('knowledge-base.create') }}" id="btn-create-article" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-md active:scale-95 flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus"></i> {{ __('Add Resource') }}
            </a>
        </div>
    </div>

    {{-- Privacy Notice Banner --}}
    <div class="flex items-start gap-3 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/60 rounded-2xl p-4 shadow-sm">
        <div class="w-8 h-8 rounded-lg bg-blue-100/80 text-blue-600 flex items-center justify-center text-sm shrink-0 shadow-sm">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="space-y-0.5">
            <h4 class="text-xs font-black text-blue-900 uppercase tracking-wide">{{ __('Document Privacy Notice') }}</h4>
            <p class="text-[11px] font-bold text-blue-700/80 leading-relaxed">{{ __('All custom documents uploaded by you are strictly private. They can only be accessed and managed by your account, and are hidden from administrators and other users.') }}</p>
        </div>
    </div>

    {{-- ===== KNOWLEDGE BASE CONTENT ===== --}}
    <div class="space-y-4">
        {{-- Search and Filter --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <form action="{{ route('knowledge-base.index') }}" method="GET" class="space-y-4" x-data x-on:change="$el.requestSubmit()">
                <div class="flex flex-col lg:flex-row gap-3">
                    <div class="flex-1 relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors text-sm">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <input 
                            type="text" 
                            name="q"
                            id="kb-search-bar"
                            value="{{ $search ?? '' }}"
                            x-on:input.debounce.500ms="$el.closest('form').requestSubmit()"
                            placeholder="{{ __('Search knowledge assets, templates, or compliance guides...') }}"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-12 {{ !empty($search) ? 'pr-10' : 'pr-4' }} py-3 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/5 transition-all"
                        >
                        @if(!empty($search))
                            <a href="{{ route('knowledge-base.index', array_merge(request()->except(['q', 'page']))) }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fa-solid fa-circle-xmark text-sm"></i>
                            </a>
                        @endif
                        @if(($selectedSource ?? 'all') !== 'all')
                            <input type="hidden" name="source" value="{{ $selectedSource }}">
                        @endif
                    </div>

                    <div class="relative min-w-44">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">
                            <i class="fa-solid fa-tag"></i>
                        </div>
                        <select name="category" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-9 py-3 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/5 transition-all cursor-pointer">
                            <option value="all" {{ ($selectedCategory ?? 'all') === 'all' ? 'selected' : '' }}>{{ __('All Categories') }}</option>
                            @foreach($categoryCounts as $category => $count)
                                <option value="{{ $category }}" {{ ($selectedCategory ?? 'all') === $category ? 'selected' : '' }}>{{ $category === 'sop' ? 'SOP' : __(ucfirst($category)) }} ({{ $count }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative min-w-48">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">
                            <i class="fa-solid fa-arrow-down-wide-short"></i>
                        </div>
                        <select name="sort" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-9 py-3 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/5 transition-all cursor-pointer">
                            <option value="latest" {{ ($selectedSort ?? 'latest') === 'latest' ? 'selected' : '' }}>{{ __('Latest Updated') }}</option>
                            <option value="title" {{ ($selectedSort ?? 'latest') === 'title' ? 'selected' : '' }}>{{ __('Title A-Z') }}</option>
                            <option value="most_downloaded" {{ ($selectedSort ?? 'latest') === 'most_downloaded' ? 'selected' : '' }}>{{ __('Most Downloaded') }}</option>
                        </select>
                    </div>

                </div>

                @if(($search ?? '') !== '' || ($selectedCategory ?? 'all') !== 'all' || ($selectedSort ?? 'latest') !== 'latest' || ($selectedSource ?? 'all') !== 'all')
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-indigo-700">
                        <span>{{ __('Showing') }} {{ $filteredCount }} {{ __('of') }} {{ $totalCount }} {{ __('resources') }}</span>
                        @if(($search ?? '') !== '')
                            <span>{{ __('Search') }}: "{{ $search }}"</span>
                        @endif
                        @if(($selectedSort ?? 'latest') !== 'latest')
                            <span>{{ __('Sort') }}: {{ __(str_replace('_', ' ', ucfirst($selectedSort))) }}</span>
                        @endif
                        @if(($selectedCategory ?? 'all') !== 'all')
                            <span>{{ __('Category') }}: {{ $selectedCategory === 'sop' ? 'SOP' : __(ucfirst($selectedCategory)) }}</span>
                        @endif
                        @if(($selectedSource ?? 'all') !== 'all')
                            <span>{{ __('Source') }}: {{ __(ucfirst($selectedSource)) }}</span>
                        @endif
                    </div>
                @endif

                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <a href="{{ route('knowledge-base.index', array_filter(['q' => $search ?: null, 'category' => ($selectedCategory ?? 'all') !== 'all' ? $selectedCategory : null, 'sort' => ($selectedSort ?? 'latest') !== 'latest' ? $selectedSort : null])) }}"
                        @class([
                            'px-4 py-2.5 rounded-xl font-black text-[9px] uppercase tracking-widest transition-all flex items-center gap-2 whitespace-nowrap',
                            'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' => ($selectedSource ?? 'all') === 'all',
                            'bg-slate-100 text-slate-500 hover:bg-slate-200' => ($selectedSource ?? 'all') !== 'all',
                        ])>
                        <i class="fa-solid fa-layer-group"></i> {{ __('All Sources') }} ({{ $totalCount }})
                    </a>

                    <a href="{{ route('knowledge-base.index', array_filter(['q' => $search ?: null, 'category' => ($selectedCategory ?? 'all') !== 'all' ? $selectedCategory : null, 'source' => 'official', 'sort' => ($selectedSort ?? 'latest') !== 'latest' ? $selectedSort : null])) }}"
                        @class([
                            'px-4 py-2.5 rounded-xl font-black text-[9px] uppercase tracking-widest transition-all flex items-center gap-2 whitespace-nowrap',
                            'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' => ($selectedSource ?? 'all') === 'official',
                            'bg-slate-100 text-slate-500 hover:bg-slate-200' => ($selectedSource ?? 'all') !== 'official',
                        ])>
                        <i class="fa-solid fa-circle-check"></i> {{ __('Official Only') }} ({{ $statistics['system_resources'] }})
                    </a>

                    <a href="{{ route('knowledge-base.index', array_filter(['q' => $search ?: null, 'category' => ($selectedCategory ?? 'all') !== 'all' ? $selectedCategory : null, 'source' => 'custom', 'sort' => ($selectedSort ?? 'latest') !== 'latest' ? $selectedSort : null])) }}"
                        @class([
                            'px-4 py-2.5 rounded-xl font-black text-[9px] uppercase tracking-widest transition-all flex items-center gap-2 whitespace-nowrap',
                            'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' => ($selectedSource ?? 'all') === 'custom',
                            'bg-slate-100 text-slate-500 hover:bg-slate-200' => ($selectedSource ?? 'all') !== 'custom',
                        ])>
                        <i class="fa-solid fa-user-pen"></i> {{ __('Custom Only') }} ({{ $statistics['user_resources'] }})
                    </a>
                </div>
            </form>
        </div>

        {{-- Resources Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <template x-for="res in resources" :key="res.id">
                <div @click="typeof Turbo !== 'undefined' ? Turbo.visit(res.show_url) : window.location.href = res.show_url"
                     class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-lg hover:scale-[1.01] transition-all group flex flex-col justify-between cursor-pointer">
                    <div>
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                                    <template x-if="res.is_system">
                                        <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[8px] font-black uppercase tracking-widest border border-indigo-100 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i>{{ __('Official') }}</span>
                                    </template>
                                    <template x-if="!res.is_system">
                                        <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded text-[8px] font-black uppercase tracking-widest border border-slate-200 flex items-center gap-1">{{ __('Custom') }}</span>
                                    </template>
                                    <span class="px-1.5 py-0.5 bg-slate-50 text-slate-400 rounded text-[8px] font-black uppercase tracking-widest border border-slate-100/60" x-text="res.category_label"></span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors leading-snug mb-1" x-text="res.title"></h3>
                                <p class="text-xs text-slate-500 font-medium leading-snug" x-text="res.desc"></p>
                            </div>
                            <div class="ml-3 px-2 py-1 bg-slate-50 text-slate-400 rounded-lg text-[8px] font-black uppercase tracking-widest border border-slate-100 shrink-0" x-text="res.type"></div>
                        </div>
                        <template x-if="res.has_attachment">
                            <div class="mb-3 inline-flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-[9px] font-black uppercase tracking-widest text-emerald-700">
                                <i class="fa-solid fa-paperclip"></i>
                                <span>{{ __('Original file attached') }}</span>
                            </div>
                        </template>
                    </div>
                    <div class="pt-3 border-t border-slate-100 mt-3 space-y-2">
                        {{-- Metadata Row --}}
                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-slate-400 text-[8px] font-bold uppercase tracking-widest">
                            <div class="flex items-center gap-1">
                                <i class="fa-regular fa-file-lines"></i>
                                <span x-text="res.downloads"></span> {{ __('Downloads') }}
                            </div>
                            <template x-if="res.size">
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-200">•</span>
                                    <i class="fa-solid fa-database"></i>
                                    <span x-text="res.size"></span>
                                </div>
                            </template>
                            <template x-if="res.updated_at">
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-200">•</span>
                                    <i class="fa-regular fa-calendar"></i>
                                    <span x-text="res.updated_at"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Action Buttons Row --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                            <div></div>
                            <div class="flex flex-wrap items-center gap-1.5">
                                {{-- Delete Form (hidden, triggered by button) --}}
                                <template x-if="!res.is_system">
                                    <form :id="'delete-form-' + res.id" :action="res.delete_url" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </template>

                                <template x-if="res.is_system">
                                    <a :href="res.download_url" data-turbo="false" @click.stop class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center hover:bg-indigo-600 transition-all shadow-md" title="{{ __('Download PDF') }}">
                                        <i class="fa-solid fa-download text-xs"></i>
                                    </a>
                                </template>

                                <template x-if="res.has_attachment">
                                    <a :href="res.attachment_url" data-turbo="false" @click.stop class="w-8 h-8 bg-emerald-50 text-emerald-700 rounded-lg flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="{{ __('Download Attachment') }}">
                                        <i class="fa-solid fa-paperclip text-xs"></i>
                                    </a>
                                </template>

                                <template x-if="!res.is_system">
                                    <div class="flex items-center gap-1.5">
                                        <a :href="res.edit_url" @click.stop class="w-8 h-8 bg-slate-100 text-slate-400 rounded-lg flex items-center justify-center hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm" title="{{ __('Edit') }}">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </a>
                                        <button @click.stop="
                                            Swal.fire({
                                                title: '{{ addslashes(__('Delete Document?')) }}',
                                                text: '{{ addslashes(__('Are you sure you want to delete document "')) }}' + res.title + '{{ addslashes(__('"? This action cannot be undone.')) }}',
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
                                                    document.getElementById('delete-form-' + res.id).submit();
                                                }
                                            });
                                        " class="w-8 h-8 bg-slate-100 text-slate-400 rounded-lg flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-all shadow-sm" title="{{ __('Delete') }}">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty State --}}
        <div x-show="resources.length === 0" x-transition class="py-16 text-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
            <i class="fa-solid fa-magnifying-glass text-3xl text-slate-200 mb-3 block"></i>
            <h3 class="text-slate-900 font-bold text-sm tracking-tight">{{ __('No Assets Found') }}</h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('Adjust your search or filter parameters') }}</p>
        </div>

        @if($resources->hasPages())
            <div class="pt-2">
                {{ $resources->links() }}
            </div>
        @endif
    </div>
</div> <!-- Close x-data container -->
@endsection
