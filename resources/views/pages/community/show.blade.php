@extends('layouts.app')
@section('title', $resource->title . ' - Community Asset')
@section('view_name', 'Community Hub')

@section('content')
<div class="w-full space-y-4 pb-10">
    {{-- Top Action Navigation Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <a href="{{ route('community.index') }}" 
           class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-600 hover:text-indigo-600 border border-slate-200/80 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm active:scale-95 w-max">
            <i class="fa-solid fa-arrow-left text-[9px]"></i> {{ __('Back to Hub') }}
        </a>
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->id() === $resource->user_id || auth()->user()->is_admin)
                <a href="{{ route('community.edit', $resource->id) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200/80 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/20 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-sm">
                    <i class="fa-solid fa-pen-to-square"></i> {{ __('Edit Asset') }}
                </a>
                <form action="{{ route('community.destroy', $resource->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this asset?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-rose-100 text-rose-600 hover:bg-rose-50 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-sm">
                        <i class="fa-solid fa-trash-can"></i> {{ __('Delete') }}
                    </button>
                </form>
            @endif
            @if($resource->content_data)
            <form action="{{ route('community.clone', $resource->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="{{ __('Clone as new session') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 border border-indigo-700 text-white hover:bg-indigo-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-down"></i> {{ __('Clone Session') }}
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Main Premium Article --}}
    <article class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-100/50 overflow-hidden">
        {{-- Hero Header Area --}}
        <div class="p-6 md:p-8 pb-4 border-b border-slate-100 bg-slate-50/50 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-32 h-32 bg-gradient-to-br from-indigo-500/5 to-transparent rounded-full blur-2xl pointer-events-none"></div>

            @php
                $readingTime = max(1, ceil(str_word_count(strip_tags($resource->content ?? $resource->description)) / 150));
            @endphp

            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="px-2.5 py-0.5 rounded-full border bg-indigo-50/80 text-indigo-700 border-indigo-100 text-[8px] font-black uppercase tracking-widest shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up mr-1"></i>{{ __('Community Asset') }}
                </span>
                @if($resource->format)
                <span class="px-2.5 py-0.5 bg-slate-100/80 text-slate-600 rounded-full text-[8px] font-black uppercase tracking-widest border border-slate-200/60 shadow-sm">
                    <i class="fa-solid fa-file mr-1"></i>{{ strtoupper($resource->format) }}
                </span>
                @endif
                
                @if($resource->tags)
                    @foreach(array_slice($resource->tags, 0, 3) as $tag)
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[7px] font-black uppercase border border-slate-200 shrink-0">{{ $tag }}</span>
                    @endforeach
                @endif
            </div>

            <h1 class="text-xl md:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                {{ $resource->title }}
            </h1>

            @if($resource->description)
                <p class="text-xs font-medium text-slate-500 mt-2 leading-relaxed max-w-3xl">
                    {{ $resource->description }}
                </p>
            @endif

            {{-- Article Metadata Bar --}}
            <div class="flex flex-wrap items-center gap-y-1.5 gap-x-3 mt-4 pt-4 border-t border-slate-100 text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                <div class="flex items-center gap-1 text-slate-600">
                    <i class="fa-solid fa-user-pen text-indigo-400"></i>
                    <span>{{ __('by') }} {{ $resource->author_name ?? ($resource->user->name ?? 'Anonymous') }}</span>
                </div>
                <span class="text-slate-200">|</span>
                <div class="flex items-center gap-1 text-amber-500">
                    <i class="fa-solid fa-star text-amber-400"></i>
                    <span>{{ number_format($resource->avg_rating, 1) }} ({{ $resource->rating_count }})</span>
                </div>
                <span class="text-slate-200">|</span>
                <div class="flex items-center gap-1 text-indigo-500">
                    <i class="fa-solid fa-arrow-up text-indigo-400"></i>
                    <span>{{ number_format($resource->upvotes) }} {{ __('upvotes') }}</span>
                </div>
                <span class="text-slate-200">|</span>
                <div class="flex items-center gap-1">
                    <i class="fa-regular fa-clock text-slate-400"></i>
                    <span>{{ $readingTime }} {{ __('min read') }}</span>
                </div>
                <span class="text-slate-200">|</span>
                <div class="flex items-center gap-1">
                    <i class="fa-solid fa-cloud-arrow-down text-slate-400"></i>
                    <span>{{ $resource->downloads_count }} {{ __('downloads') }}</span>
                </div>
                @if($resource->size)
                    <span class="text-slate-200">|</span>
                    <div class="flex items-center gap-1">
                        <i class="fa-solid fa-server text-slate-400"></i>
                        <span>{{ $resource->size }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Premium Article Content Area --}}
        <div class="px-6 md:px-8 py-6 bg-white">
            @if(trim(strip_tags($contentHtml)) !== '' && $resource->content)
                <div class="prose prose-slate prose-indigo max-w-none text-slate-700 leading-relaxed
                            prose-headings:font-black prose-headings:text-slate-900 prose-headings:tracking-tight
                            prose-h2:text-xl prose-h2:border-b prose-h2:border-slate-100 prose-h2:pb-2 prose-h2:mt-6 prose-h2:mb-4
                            prose-h3:text-base prose-h3:mt-4 prose-h3:mb-2
                            prose-p:text-slate-600 prose-p:leading-relaxed prose-p:mb-4
                            prose-li:text-slate-600 prose-li:my-0.5
                            prose-strong:text-slate-950 prose-strong:font-extrabold
                            prose-blockquote:border-l-4 prose-blockquote:border-indigo-500 prose-blockquote:bg-slate-50 prose-blockquote:px-4 prose-blockquote:py-2 prose-blockquote:rounded-r-xl prose-blockquote:text-slate-600 prose-blockquote:not-italic">
                    {!! $contentHtml !!}
                </div>

                @if(filled($resource->attachment_path) && in_array(strtolower($resource->format), ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']))
                    <div class="mt-8 pt-8 border-t border-slate-100 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-slate-900 font-bold text-sm tracking-tight flex items-center gap-2">
                                <i class="fa-solid fa-paperclip text-indigo-600"></i>
                                {{ app()->getLocale() == 'id' ? 'Lampiran Dokumen' : 'Document Attachment' }}
                            </h3>
                            <a href="{{ route('community.attachment', $resource->id) }}?download=1" data-turbo="false"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 border border-emerald-200 text-emerald-700 hover:text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">
                                <i class="fa-solid fa-download"></i> {{ __('Download') }}
                            </a>
                        </div>
                        @if(strtolower($resource->format) === 'pdf')
                            <div class="w-full bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 shadow-inner">
                                <iframe src="{{ route('community.attachment', $resource->id) }}" class="w-full h-[600px] border-none" allow="autoplay"></iframe>
                            </div>
                        @else
                            <div class="w-full bg-slate-50 rounded-2xl p-4 border border-slate-200 flex justify-center items-center">
                                <img src="{{ route('community.attachment', $resource->id) }}" class="max-w-full max-h-[500px] rounded-xl shadow-sm object-contain" alt="{{ $resource->attachment_name }}">
                            </div>
                        @endif
                    </div>
                @endif
            @else
                @if(filled($resource->attachment_path) && in_array(strtolower($resource->format), ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']))
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-slate-900 font-bold text-sm tracking-tight flex items-center gap-2">
                                <i class="fa-solid fa-file-pdf text-indigo-600"></i>
                                {{ $resource->attachment_name }}
                            </h3>
                            <a href="{{ route('community.attachment', $resource->id) }}?download=1" data-turbo="false"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 border border-emerald-200 text-emerald-700 hover:text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">
                                <i class="fa-solid fa-download"></i> {{ __('Download') }}
                            </a>
                        </div>
                        @if(strtolower($resource->format) === 'pdf')
                            <div class="w-full bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 shadow-inner">
                                <iframe src="{{ route('community.attachment', $resource->id) }}" class="w-full h-[600px] border-none" allow="autoplay"></iframe>
                            </div>
                        @else
                            <div class="w-full bg-slate-50 rounded-2xl p-4 border border-slate-200 flex justify-center items-center">
                                <img src="{{ route('community.attachment', $resource->id) }}" class="max-w-full max-h-[500px] rounded-xl shadow-sm object-contain" alt="{{ $resource->attachment_name }}">
                            </div>
                        @endif
                    </div>
                @elseif(filled($resource->attachment_path))
                    <div class="py-8 text-center bg-slate-50 rounded-2xl border border-slate-100 p-6 max-w-2xl mx-auto">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-indigo-100/50">
                            <i class="fa-solid fa-file-arrow-down text-lg"></i>
                        </div>
                        <h3 class="text-slate-950 font-bold text-sm tracking-tight">
                            {{ app()->getLocale() == 'id' ? 'Dokumen Lampiran Saja' : 'Attachment-Only Document' }}
                        </h3>
                        <p class="text-xs text-slate-500 font-medium leading-relaxed mt-2">
                            {{ app()->getLocale() == 'id' 
                                ? 'Aset ini berupa file lampiran (' . $resource->format . ') dan tidak dapat dipratinjau secara langsung. Silakan unduh dokumen untuk melihat detail lengkap.' 
                                : 'This asset is an attachment (' . $resource->format . ') and cannot be previewed directly. Please download the document to view its full details.' }}
                        </p>
                        <div class="mt-4 flex justify-center">
                            <a href="{{ route('community.attachment', $resource->id) }}" data-turbo="false"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 hover:bg-emerald-600 border border-emerald-200 text-emerald-700 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">
                                <i class="fa-solid fa-cloud-arrow-down"></i>
                                {{ __('Download Attachment') }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center bg-slate-50 rounded-2xl border border-slate-100 p-6 max-w-2xl mx-auto">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-indigo-100/50">
                            <i class="fa-solid fa-info-circle text-lg"></i>
                        </div>
                        <h3 class="text-slate-950 font-bold text-sm tracking-tight">
                            {{ app()->getLocale() == 'id' ? 'Tidak Ada Detail' : 'No Details Available' }}
                        </h3>
                        <p class="text-xs text-slate-500 font-medium leading-relaxed mt-2">
                            {{ app()->getLocale() == 'id' 
                                ? 'Aset ini tidak memiliki konten atau dokumen lampiran.' 
                                : 'This asset does not contain any content or document attachment.' }}
                        </p>
                    </div>
                @endif
            @endif
        </div>
    </article>
</div>
@endsection
