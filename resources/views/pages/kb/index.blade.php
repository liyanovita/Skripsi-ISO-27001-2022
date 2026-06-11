@extends('layouts.app')
@section('title', 'Knowledge Base')
@section('view_name', 'Knowledge Base')

@section('content')
<div class="max-w-6xl mx-auto space-y-4 pb-8" x-data="{ 
    showReadModal: false,
    activeArticle: null,
    resources: @js($resources->getCollection()->map(fn($r) => [
        'id' => $r->id,
        'title' => $r->title,
        'category' => $r->category,
        'category_label' => __(ucfirst($r->category)),
        'type' => $r->format ? strtoupper($r->format) : 'PDF',
        'desc' => $r->description ?? '',
        'content' => $r->content,
        'content_html' => (string) Str::markdown(e($r->content)),
        'size' => $r->size ?? '',
        'has_attachment' => filled($r->attachment_path),
        'attachment_name' => $r->attachment_name ?? '',
        'attachment_url' => filled($r->attachment_path) ? route('knowledge-base.attachment', $r->id) : null,
        'downloads' => $r->downloads_count,
        'updated_at' => $r->updated_at?->format('d M Y') ?? '',
        'is_system' => $r->is_system,
        'edit_url' => route('knowledge-base.edit', $r->id),
        'delete_url' => route('knowledge-base.destroy', $r->id),
        'download_url' => route('knowledge-base.download', $r->id)
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
            <a href="{{ route('knowledge-base.export-json') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-export"></i> {{ __('Export') }}
            </a>
            <form action="{{ route('knowledge-base.import-json') }}" method="POST" enctype="multipart/form-data" class="flex">
                @csrf
                <label class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-file-import"></i> {{ __('Import') }}
                    <input type="file" name="json_file" accept="application/json,.json" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <a href="{{ route('knowledge-base.create') }}" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-md active:scale-95 flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus"></i> {{ __('Add Resource') }}
            </a>
        </div>
    </div>

    {{-- ===== KNOWLEDGE BASE CONTENT ===== --}}
    <div class="space-y-4">
        {{-- Search and Filter --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <form action="{{ route('knowledge-base.index') }}" method="GET" class="space-y-4" x-data x-on:change="$el.submit()">
                <div class="flex flex-col lg:flex-row gap-3">
                    <div class="flex-1 relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors text-sm">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <input 
                            type="text" 
                            name="q"
                            value="{{ $search ?? '' }}"
                            x-on:input.debounce.500ms="$el.closest('form').submit()"
                            placeholder="{{ __('Search knowledge assets, templates, or compliance guides...') }}"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-4 py-3 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/5 transition-all"
                        >
                        @if(($selectedCategory ?? 'all') !== 'all')
                            <input type="hidden" name="category" value="{{ $selectedCategory }}">
                        @endif
                    </div>

                    <div class="relative min-w-44">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">
                            <i class="fa-solid fa-certificate"></i>
                        </div>
                        <select name="source" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-9 py-3 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/5 transition-all cursor-pointer">
                            <option value="all" {{ ($selectedSource ?? 'all') === 'all' ? 'selected' : '' }}>{{ __('All Sources') }}</option>
                            <option value="official" {{ ($selectedSource ?? 'all') === 'official' ? 'selected' : '' }}>{{ __('Official Only') }}</option>
                            <option value="custom" {{ ($selectedSource ?? 'all') === 'custom' ? 'selected' : '' }}>{{ __('Custom Only') }}</option>
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
                        @if(($selectedSource ?? 'all') !== 'all')
                            <span>{{ __('Source') }}: {{ __(ucfirst($selectedSource)) }}</span>
                        @endif
                    </div>
                @endif

                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <a href="{{ route('knowledge-base.index', array_filter(['q' => $search ?: null, 'source' => ($selectedSource ?? 'all') !== 'all' ? $selectedSource : null, 'sort' => ($selectedSort ?? 'latest') !== 'latest' ? $selectedSort : null])) }}"
                        @class([
                            'px-4 py-2.5 rounded-xl font-black text-[9px] uppercase tracking-widest transition-all flex items-center gap-2 whitespace-nowrap',
                            'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' => ($selectedCategory ?? 'all') === 'all',
                            'bg-slate-100 text-slate-500 hover:bg-slate-200' => ($selectedCategory ?? 'all') !== 'all',
                        ])>
                        <i class="fa-solid fa-layer-group"></i> All ({{ $totalCount }})
                    </a>
                    @foreach($categoryCounts as $category => $count)
                    <a href="{{ route('knowledge-base.index', array_filter(['q' => $search ?: null, 'category' => $category, 'source' => ($selectedSource ?? 'all') !== 'all' ? $selectedSource : null, 'sort' => ($selectedSort ?? 'latest') !== 'latest' ? $selectedSort : null])) }}"
                        @class([
                            'px-4 py-2.5 rounded-xl font-black text-[9px] uppercase tracking-widest transition-all flex items-center gap-2 whitespace-nowrap',
                            'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' => ($selectedCategory ?? 'all') === $category,
                            'bg-slate-100 text-slate-500 hover:bg-slate-200' => ($selectedCategory ?? 'all') !== $category,
                        ])>
                        <i class="fa-solid {{ str_contains($category, 'template') ? 'fa-file-signature' : (str_contains($category, 'guide') ? 'fa-book-bookmark' : 'fa-shield-halved') }}"></i>
                        {{ __(ucfirst($category)) }} ({{ $count }})
                    </a>
                    @endforeach
                </div>
            </form>
        </div>

        {{-- Resources Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <template x-for="res in resources" :key="res.id">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-lg hover:scale-[1.01] transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <template x-if="res.is_system">
                                        <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[8px] font-black uppercase tracking-widest border border-indigo-100 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i>{{ __('Official') }}</span>
                                    </template>
                                    <template x-if="!res.is_system">
                                        <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded text-[8px] font-black uppercase tracking-widest border border-slate-200">{{ __('Custom') }}</span>
                                    </template>
                                    <h3 class="text-sm font-bold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors leading-none cursor-pointer" x-text="res.title" @click="activeArticle = res; showReadModal = true;"></h3>
                                </div>
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
                    <div class="flex items-center justify-between pt-4 border-t border-slate-50 mt-3">
                        <div class="flex items-center gap-2 text-slate-400">
                            <i class="fa-regular fa-file-lines text-xs"></i>
                            <span class="text-[9px] font-bold uppercase tracking-widest" x-text="res.downloads + ' {{ __('downloads') }}'"></span>
                            <template x-if="res.size">
                                <span class="text-[9px] font-bold uppercase tracking-widest" x-text="' - ' + res.size"></span>
                            </template>
                            <template x-if="res.updated_at">
                                <span class="text-[9px] font-bold uppercase tracking-widest" x-text="' - ' + res.updated_at"></span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- Delete Form (hidden, triggered by button) --}}
                            <template x-if="!res.is_system">
                                <form :id="'delete-form-' + res.id" :action="res.delete_url" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </template>

                            <button @click="activeArticle = res; showReadModal = true;" class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                <i class="fa-solid fa-eye text-[9px]"></i>{{ __('Read') }}</button>

                            <a :href="res.download_url" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 text-white rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-md">
                                <i class="fa-solid fa-file-pdf text-[9px]"></i>{{ __('PDF') }}</a>

                            <template x-if="res.has_attachment">
                                <a :href="res.attachment_url" class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-paperclip text-[9px]"></i>{{ __('File') }}</a>
                            </template>

                            <template x-if="!res.is_system">
                                <div class="flex items-center gap-2">
                                    <a :href="res.edit_url" class="w-7 h-7 bg-slate-100 text-slate-400 rounded-lg flex items-center justify-center hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm" title="{{ __('Edit') }}">
                                        <i class="fa-solid fa-pen text-[9px]"></i>
                                    </a>
                                    <button @click="if(confirm('{{ __('Are you sure you want to delete this resource?') }}')) document.getElementById('delete-form-' + res.id).submit();" class="w-7 h-7 bg-slate-100 text-slate-400 rounded-lg flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-all shadow-sm" title="{{ __('Delete') }}">
                                        <i class="fa-solid fa-trash text-[9px]"></i>
                                    </button>
                                </div>
                            </template>
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


    {{-- Read Viewer Modal --}}
    <div x-show="showReadModal" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak>
        
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md" @click="showReadModal = false"></div>

        <div class="relative bg-white rounded-[24px] border border-slate-100 w-full max-w-4xl p-6 md:p-8 shadow-2xl z-10 flex flex-col h-[85vh]" @click.away="showReadModal = false">
            {{-- Header --}}
            <div class="flex items-start justify-between border-b border-slate-100 pb-5 mb-5 shrink-0">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-1 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-widest" x-text="activeArticle?.type"></span>
                        <span class="px-2 py-1 bg-indigo-50 text-indigo-600 rounded text-[9px] font-black uppercase tracking-widest" x-text="activeArticle?.category_label"></span>
                        <template x-if="activeArticle?.has_attachment">
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded text-[9px] font-black uppercase tracking-widest">
                                <i class="fa-solid fa-paperclip mr-1"></i>{{ __('Attachment') }}
                            </span>
                        </template>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight" x-text="activeArticle?.title"></h2>
                    <p class="text-sm font-medium text-slate-500 mt-1" x-text="activeArticle?.desc"></p>
                </div>
                <button @click="showReadModal = false" class="w-8 h-8 bg-slate-50 hover:bg-slate-200 text-slate-500 rounded-full flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar relative">
                <div class="prose prose-sm prose-slate max-w-none text-slate-700 leading-relaxed p-4 bg-slate-50 rounded-xl border border-slate-100" x-html="activeArticle?.content_html">
                </div>
            </div>

            {{-- Footer --}}
            <div class="pt-5 mt-5 border-t border-slate-100 flex items-center justify-between shrink-0">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <i class="fa-solid fa-shield-halved mr-1"></i>{{ __('ISO 27001:2022 Official Resource') }}</span>
                <div class="flex gap-2">
                    <button @click="showReadModal = false" class="px-6 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all shadow-sm">{{ __('Close') }}</button>
                    <template x-if="activeArticle?.has_attachment">
                        <a :href="activeArticle?.attachment_url" class="px-6 py-2.5 bg-emerald-50 text-emerald-700 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-paperclip"></i>{{ __('Download File') }}</a>
                    </template>
                    <a :href="activeArticle?.download_url" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-download"></i>{{ __('Download PDF') }}</a>
                </div>
            </div>
        </div>
    </div>

</div> <!-- Close x-data container -->
@endsection
