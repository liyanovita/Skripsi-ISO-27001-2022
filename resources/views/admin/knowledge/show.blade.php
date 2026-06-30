@extends('layouts.admin')
@section('title', $resource->title . ' - Knowledge Base')
@section('header_title', __('View Knowledge Base Item'))

@section('content')
<div class="w-full space-y-5 pb-10">
    {{-- Top Action Navigation Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.knowledge.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors font-medium">
                <i class="fa-solid fa-arrow-left"></i> {{ __('Back to Knowledge Base') }}
            </a>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.knowledge.edit', $resource) }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm active:scale-95">
                <i class="fa-solid fa-pen-to-square text-xs"></i> {{ __('Edit Article') }}
            </a>
            <form id="delete-form-{{ $resource->id }}" action="{{ route('admin.knowledge.destroy', $resource) }}" method="POST" class="inline"
                x-data
                @submit.prevent="
                    Swal.fire({
                        title: '{{ addslashes(__('Delete Document?')) }}',
                        text: '{{ addslashes(__('Are you sure you want to delete this document? This action cannot be undone.')) }}',
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
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm active:scale-95">
                    <i class="fa-solid fa-trash-can text-xs"></i> {{ __('Delete') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Admin Two-Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        {{-- Left Column: Content Area (75% width) --}}
        <div class="lg:col-span-3 space-y-6">
            <article class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                {{-- Header Section --}}
                <div class="p-6 border-b border-slate-200 bg-slate-50/50">
                    <h1 class="text-xl font-bold text-slate-800 leading-tight">
                        {{ $resource->title }}
                    </h1>
                    @if($resource->description)
                        <p class="text-xs font-normal text-slate-500 mt-2 leading-relaxed">
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
                                    prose-blockquote:border-l-4 prose-blockquote:border-slate-300 prose-blockquote:bg-slate-50 prose-blockquote:px-4 prose-blockquote:py-2 prose-blockquote:rounded-r-lg prose-blockquote:text-slate-500 prose-blockquote:not-italic">
                            {!! $contentHtml !!}
                        </div>
                    @else
                        <div class="py-12 text-center text-slate-400 italic text-sm">
                            <i class="fa-solid fa-file-signature text-3xl mb-3 text-slate-300 block"></i>
                            {{ __('This document has no article body content.') }}
                        </div>
                    @endif
                </div>
            </article>

            {{-- Attachment Box --}}
            @if(filled($resource->attachment_path))
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <h3 class="text-slate-800 font-bold text-sm flex items-center gap-2">
                            <i class="fa-solid fa-paperclip text-slate-400"></i>
                            {{ __('Attachment Preview') }}
                        </h3>
                        <a href="{{ route('knowledge-base.attachment', $resource->id) }}?download=1" data-turbo="false"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 border border-emerald-200 text-emerald-700 hover:text-white rounded-lg text-xs font-bold transition-all shadow-sm active:scale-95">
                            <i class="fa-solid fa-download"></i> {{ __('Download Original File') }}
                        </a>
                    </div>
                    <div class="p-6 bg-slate-50/50">
                        @if(strtolower($resource->format) === 'pdf')
                            <div class="w-full bg-slate-100 rounded-lg overflow-hidden border border-slate-200 shadow-inner">
                                <iframe src="{{ route('knowledge-base.attachment', $resource->id) }}" class="w-full h-[600px] border-none" allow="autoplay"></iframe>
                            </div>
                        @elseif(in_array(strtolower($resource->format), ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']))
                            <div class="w-full bg-white rounded-lg p-4 border border-slate-200 flex justify-center items-center">
                                <img src="{{ route('knowledge-base.attachment', $resource->id) }}" class="max-w-full max-h-[500px] rounded-lg shadow-sm object-contain" alt="{{ $resource->attachment_name }}">
                            </div>
                        @else
                            <div class="py-10 text-center bg-white rounded-lg border border-slate-200 p-6 max-w-md mx-auto shadow-sm">
                                @php
                                    $excelCsvIcon = match(strtolower($resource->format)) {
                                        'xls', 'xlsx' => 'fa-solid fa-file-excel text-emerald-600 bg-emerald-50 border-emerald-100',
                                        'csv' => 'fa-solid fa-file-csv text-teal-600 bg-teal-50 border-teal-100',
                                        'doc', 'docx' => 'fa-solid fa-file-word text-blue-600 bg-blue-50 border-blue-100',
                                        default => 'fa-solid fa-file-arrow-down text-slate-600 bg-slate-50 border-slate-200',
                                    };
                                    $iconParts = explode(' ', $excelCsvIcon);
                                @endphp
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-4 border {{ $iconParts[3] ?? 'border-slate-200' }} {{ $iconParts[4] ?? 'bg-slate-50' }}">
                                    <i class="{{ $iconParts[0] }} {{ $iconParts[1] }} {{ $iconParts[2] }} text-lg"></i>
                                </div>
                                <h4 class="text-slate-800 font-bold text-sm truncate max-w-xs mx-auto" title="{{ $resource->attachment_name }}">
                                    {{ $resource->attachment_name }}
                                </h4>
                                <div class="text-[10px] text-slate-400 font-medium uppercase mt-1">
                                    {{ $resource->format ? strtoupper($resource->format) : 'UNKNOWN' }} &bull; {{ $resource->size }}
                                </div>
                                <p class="text-xs text-slate-500 mt-3 leading-relaxed max-w-sm mx-auto">
                                    {{ app()->getLocale() == 'id' 
                                        ? 'Dokumen dengan format ' . strtoupper($resource->format) . ' tidak dapat dipratinjau secara langsung di sistem. Silakan unduh dokumen untuk melihat detail lengkap.' 
                                        : 'Documents with ' . strtoupper($resource->format) . ' format cannot be previewed directly in the browser. Please download the document to view its full details.' }}
                                </p>
                                <div class="mt-5">
                                    <a href="{{ route('knowledge-base.attachment', $resource->id) }}?download=1" data-turbo="false"
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm active:scale-95">
                                        <i class="fa-solid fa-cloud-arrow-down"></i>
                                        {{ __('Download File') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column: Admin Sidebar Properties (25% width) --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-slate-800 font-bold text-sm flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-slate-400"></i>
                        {{ __('Document Properties') }}
                    </h3>
                </div>
                
                <div class="p-5 space-y-4 text-xs text-slate-600">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Document ID') }}</div>
                        <div class="font-mono text-slate-800 text-sm mt-0.5">#{{ $resource->id }}</div>
                    </div>

                    <hr class="border-slate-100">

                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Category') }}</div>
                        <div class="mt-1">
                            @php
                                $categoryColors = match($resource->category) {
                                    'guides' => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'templates' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    'sop' => 'bg-amber-50 text-amber-700 border-amber-100',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider {{ $categoryColors }}">
                                {{ $resource->category === 'sop' ? 'SOP' : __(ucfirst($resource->category)) }}
                            </span>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('File Format') }}</div>
                        <div class="font-bold text-slate-800 mt-0.5 uppercase">{{ $resource->format ?: 'HTML / ARTICLE' }}</div>
                    </div>

                    <hr class="border-slate-100">

                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Downloads') }}</div>
                        <div class="font-bold text-slate-800 mt-0.5">{{ number_format($resource->downloads_count) }}</div>
                    </div>

                    <hr class="border-slate-100">

                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Created At') }}</div>
                        <div class="text-slate-800 mt-0.5">{{ $resource->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</div>
                    </div>

                    <hr class="border-slate-100">

                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Last Updated') }}</div>
                        <div class="text-slate-800 mt-0.5">{{ $resource->updated_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</div>
                    </div>

                    @if(filled($resource->attachment_path))
                        <hr class="border-slate-100">

                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Attachment Metadata') }}</div>
                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 space-y-2.5">
                                <div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase">{{ __('Original Name') }}</div>
                                    <div class="text-slate-800 break-all font-medium mt-0.5">{{ $resource->attachment_name }}</div>
                                </div>
                                <div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase">{{ __('MIME Type') }}</div>
                                    <div class="text-slate-700 font-mono text-[10px] break-all mt-0.5">{{ $resource->attachment_mime }}</div>
                                </div>
                                <div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase">{{ __('File Size') }}</div>
                                    <div class="text-slate-700 mt-0.5">{{ $resource->size }} ({{ number_format($resource->attachment_size) }} bytes)</div>
                                </div>
                                <div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase">{{ __('Storage Disk Path') }}</div>
                                    <div class="text-slate-600 font-mono text-[10px] break-all bg-white border border-slate-200 rounded p-1.5 mt-0.5 select-all" title="Click to select all">
                                        {{ $resource->attachment_path }}
                                    </div>
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
