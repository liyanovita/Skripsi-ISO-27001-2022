@extends('layouts.admin')

@section('title', $resource->title . ' — Knowledge Base')
@section('header_title', 'View Knowledge Base Document')

@section('content')
<div class="space-y-6 pb-8">

    {{-- Top Action Navigation Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.knowledge.index') }}"
                class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-blue-600 transition-colors font-medium group">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Back to Knowledge Base
            </a>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.knowledge.edit', $resource) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-amber-500/20 active:scale-95">
                <i class="fa-solid fa-pen text-xs"></i> Edit Document
            </a>
            <form id="delete-form-{{ $resource->id }}" action="{{ route('admin.knowledge.destroy', $resource) }}" method="POST" class="inline"
                x-data
                @submit.prevent="
                    Swal.fire({
                        title: 'Delete Document?',
                        text: 'Are you sure you want to delete &quot;{{ addslashes($resource->title) }}&quot;? This action cannot be undone.',
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
                    class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-600/20 active:scale-95">
                    <i class="fa-solid fa-trash-can text-xs"></i> Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Layout Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Left Main Content (75%) --}}
        <div class="lg:col-span-3 space-y-6">
            <article class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                {{-- Header Section --}}
                <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white">
                    <div class="flex items-center gap-2 mb-2">
                        @php
                            $catStyle = match($resource->category) {
                                'guides' => 'bg-blue-50 text-blue-700 border-blue-100',
                                'templates' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'sop' => 'bg-amber-50 text-amber-700 border-amber-100',
                                'evidence' => 'bg-blue-50 text-blue-700 border-blue-100',
                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $catStyle }}">
                            {{ $resource->category === 'sop' ? 'SOP' : ucfirst($resource->category) }}
                        </span>
                        @if($resource->format)
                            <span class="px-2 py-0.5 bg-slate-900 text-white rounded text-[10px] font-mono font-bold uppercase">
                                {{ $resource->format }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl font-black text-slate-800 leading-tight">
                        {{ $resource->title }}
                    </h1>
                    @if($resource->description)
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed font-medium">
                            {{ $resource->description }}
                        </p>
                    @endif
                </div>

                {{-- Content Body --}}
                <div class="p-6 md:p-8 bg-white">
                    @if(trim(strip_tags($contentHtml)) !== '')
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed text-sm
                                    prose-headings:font-bold prose-headings:text-slate-800
                                    prose-h2:text-lg prose-h2:border-b prose-h2:border-slate-100 prose-h2:pb-2 prose-h2:mt-6 prose-h2:mb-4
                                    prose-h3:text-base prose-h3:mt-4 prose-h3:mb-2
                                    prose-p:text-slate-600 prose-p:leading-relaxed prose-p:mb-4
                                    prose-li:text-slate-600 prose-li:my-0.5
                                    prose-strong:text-slate-800 prose-strong:font-bold
                                    prose-blockquote:border-l-4 prose-blockquote:border-blue-400 prose-blockquote:bg-blue-50/50 prose-blockquote:px-4 prose-blockquote:py-2 prose-blockquote:rounded-r-xl prose-blockquote:text-blue-900 prose-blockquote:not-italic">
                            {!! $contentHtml !!}
                        </div>
                    @else
                        <div class="py-16 text-center text-slate-400">
                            <i class="fa-solid fa-file-signature text-3xl mb-3 text-slate-200 block"></i>
                            <p class="text-xs font-medium">This document has no rich-text article content body.</p>
                        </div>
                    @endif
                </div>
            </article>

            {{-- Attachment Box --}}
            @if(filled($resource->attachment_path))
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden space-y-0">
                    <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
                        <h3 class="text-slate-800 font-bold text-sm flex items-center gap-2">
                            <i class="fa-solid fa-paperclip text-blue-500"></i> Attachment Preview
                        </h3>
                        <a href="{{ route('knowledge-base.attachment', $resource->id) }}?download=1" data-turbo="false"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-600/20 active:scale-95">
                            <i class="fa-solid fa-download text-[10px]"></i> Download File
                        </a>
                    </div>
                    <div class="p-5 bg-slate-50/40">
                        @if(strtolower($resource->format) === 'pdf')
                            <div class="w-full bg-slate-100 rounded-xl overflow-hidden border border-slate-200 shadow-inner">
                                <iframe src="{{ route('knowledge-base.attachment', $resource->id) }}" class="w-full h-[600px] border-none"></iframe>
                            </div>
                        @elseif(in_array(strtolower($resource->format), ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']))
                            <div class="w-full bg-white rounded-xl p-4 border border-slate-200 flex justify-center items-center">
                                <img src="{{ route('knowledge-base.attachment', $resource->id) }}" class="max-w-full max-h-[500px] rounded-xl shadow-sm object-contain" alt="{{ $resource->attachment_name }}">
                            </div>
                        @else
                            <div class="py-10 text-center bg-white rounded-2xl border border-slate-100 p-6 max-w-md mx-auto shadow-sm">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 text-lg font-bold">
                                    <i class="fa-solid fa-file-arrow-down"></i>
                                </div>
                                <h4 class="text-slate-800 font-bold text-sm truncate max-w-xs mx-auto" title="{{ $resource->attachment_name }}">
                                    {{ $resource->attachment_name }}
                                </h4>
                                <div class="text-[10px] text-slate-400 font-mono font-bold uppercase mt-1">
                                    {{ $resource->format ? strtoupper($resource->format) : 'FILE' }} • {{ $resource->size }}
                                </div>
                                <p class="text-xs text-slate-500 mt-2.5 leading-relaxed font-medium">
                                    This file format cannot be previewed inline. Download to inspect the full contents.
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('knowledge-base.attachment', $resource->id) }}?download=1" data-turbo="false"
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-600/20 active:scale-95">
                                        <i class="fa-solid fa-cloud-arrow-down"></i> Download File
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Admin Sidebar Properties (25%) --}}
        <div class="lg:col-span-1 space-y-5">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/80">
                    <h3 class="text-slate-800 font-bold text-sm flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-blue-500"></i> Document Properties
                    </h3>
                </div>

                <div class="p-5 space-y-3.5 text-xs">
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Document ID</span>
                        <span class="font-mono text-slate-800 font-bold">#{{ $resource->id }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Category</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $catStyle }}">
                            {{ $resource->category === 'sop' ? 'SOP' : ucfirst($resource->category) }}
                        </span>
                    </div>

                    <div class="pt-3 border-t border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">File Format</span>
                        <span class="font-bold text-slate-800 uppercase">{{ $resource->format ?: 'HTML / Rich Text' }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Total Downloads</span>
                        <span class="font-black text-blue-600 text-sm">{{ number_format($resource->downloads_count) }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Created At</span>
                        <span class="text-slate-600 font-medium">{{ $resource->created_at?->format('M d, Y H:i') ?? 'N/A' }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Last Updated</span>
                        <span class="text-slate-600 font-medium">{{ $resource->updated_at?->format('M d, Y H:i') ?? 'N/A' }}</span>
                    </div>

                    @if(filled($resource->attachment_path))
                        <div class="pt-3 border-t border-slate-50">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">Attachment Info</span>
                            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 space-y-2 text-[11px]">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Name</span>
                                    <span class="text-slate-800 font-semibold break-all">{{ $resource->attachment_name }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Size</span>
                                    <span class="text-slate-700 font-mono">{{ $resource->size }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
