@extends('layouts.app')
@section('title', $resource->title . ' - Knowledge Base')
@section('view_name', 'Knowledge Base')

@section('content')
<div class="w-full space-y-4 pb-10">
    {{-- Top Action Navigation Toolbar (Compact and Sleek) --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('knowledge-base.index') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-600 hover:text-indigo-600 border border-slate-200/80 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">
            <i class="fa-solid fa-arrow-left text-[9px]"></i> {{ __('Back') }}
        </a>
        <div class="flex items-center gap-2">
            @if(!$resource->is_system)
                <a href="{{ route('knowledge-base.edit', $resource->id) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200/80 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/20 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-sm">
                    <i class="fa-solid fa-pen-to-square"></i> {{ __('Edit Article') }}
                </a>
                <form id="delete-form-{{ $resource->id }}" action="{{ route('knowledge-base.destroy', $resource->id) }}" method="POST" class="inline"
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
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-rose-100 text-rose-600 hover:bg-rose-50 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-sm">
                        <i class="fa-solid fa-trash-can"></i> {{ __('Delete') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Main Premium Article --}}
    <article class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-100/50 overflow-hidden">
        {{-- Hero Header Area (More Compact Padding) --}}
        <div class="p-6 md:p-8 pb-4 border-b border-slate-100 bg-slate-50/50 relative overflow-hidden">
            {{-- Decorative Gradient Corner Accent --}}
            <div class="absolute right-0 top-0 w-32 h-32 bg-gradient-to-br from-indigo-500/5 to-transparent rounded-full blur-2xl pointer-events-none"></div>

            @php
                $categoryColors = match($resource->category) {
                    'guides' => 'bg-indigo-50/80 text-indigo-700 border-indigo-100',
                    'templates' => 'bg-emerald-50/80 text-emerald-700 border-emerald-100',
                    'sop' => 'bg-amber-50/80 text-amber-700 border-amber-100',
                    default => 'bg-rose-50/80 text-rose-700 border-rose-100',
                };
                
                $readingTime = max(1, ceil(str_word_count(strip_tags($resource->content)) / 150));
                $displaySize = $resource->size ?: ($resource->is_system ? round(strlen($resource->content) / 1024 + 12) . 'KB' : '');

                // Normalize FontAwesome icon class (ensure style prefix like fa-solid is present)
                $iconClass = $resource->icon ?: 'fa-file-pdf';
                if (!preg_match('/\b(fa-solid|fa-regular|fa-brands|fa-light|fa-duotone|fa-thin)\b/', $iconClass)) {
                    if (strpos($iconClass, 'fa-') !== 0) {
                        $iconClass = 'fa-' . $iconClass;
                    }
                    $iconClass = 'fa-solid ' . $iconClass;
                }
            @endphp

            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="px-2.5 py-0.5 rounded-full border text-[8px] font-black uppercase tracking-widest shadow-sm {{ $categoryColors }}">
                    <i class="fa-solid fa-folder-open mr-1"></i>{{ $resource->category === 'sop' ? 'SOP' : __(ucfirst($resource->category)) }}
                </span>
                <span class="px-2.5 py-0.5 bg-slate-100/80 text-slate-600 rounded-full text-[8px] font-black uppercase tracking-widest border border-slate-200/60 shadow-sm">
                    <i class="fa-solid fa-file-code mr-1"></i>{{ $resource->format ? strtoupper($resource->format) : 'PDF' }}
                </span>
                @if($resource->is_system)
                    <span class="px-2.5 py-0.5 bg-blue-50 text-blue-600 rounded-full text-[8px] font-black uppercase tracking-widest border border-blue-100 flex items-center gap-1 shadow-sm">
                        <i class="fa-solid fa-circle-check text-blue-500"></i> {{ __('Official') }}
                    </span>
                @else
                    <span class="px-2.5 py-0.5 bg-violet-50 text-violet-600 rounded-full text-[8px] font-black uppercase tracking-widest border border-violet-100 flex items-center gap-1 shadow-sm">
                        <i class="fa-solid fa-user-pen text-violet-500"></i> {{ __('Custom') }}
                    </span>
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

            {{-- Article Metadata Bar (Reduced top spacing) --}}
            <div class="flex flex-wrap items-center gap-y-1.5 gap-x-3 mt-4 pt-4 border-t border-slate-100 text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                <div class="flex items-center gap-1">
                    <i class="fa-regular fa-clock text-slate-400"></i>
                    <span>{{ $readingTime }} {{ __('min read') }}</span>
                </div>
                <span class="text-slate-200">|</span>
                <div class="flex items-center gap-1">
                    <i class="fa-regular fa-calendar-check text-slate-400"></i>
                    <span>{{ __('Updated') }}: {{ $resource->updated_at?->format('d M Y') ?? 'N/A' }}</span>
                </div>
                <span class="text-slate-200">|</span>
                <div class="flex items-center gap-1">
                    <i class="fa-solid fa-cloud-arrow-down text-slate-400"></i>
                    <span>{{ $resource->downloads_count }} {{ __('downloads') }}</span>
                </div>
                @if($displaySize)
                    <span class="text-slate-200">|</span>
                    <div class="flex items-center gap-1">
                        <i class="fa-solid fa-server text-slate-400"></i>
                        <span>{{ $displaySize }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Premium Article Content Area (More Compact Padding) --}}
        <div class="px-6 md:px-8 py-6 bg-white">
            @if(trim(strip_tags($contentHtml)) !== '')
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
                            <a href="{{ route('knowledge-base.attachment', $resource->id) }}?download=1" data-turbo="false"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 border border-emerald-200 text-emerald-700 hover:text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">
                                <i class="fa-solid fa-download"></i> {{ __('Download Original') }}
                            </a>
                        </div>
                        @if(strtolower($resource->format) === 'pdf')
                            <div class="w-full bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 shadow-inner">
                                <iframe src="{{ route('knowledge-base.attachment', $resource->id) }}" class="w-full h-[600px] border-none" allow="autoplay"></iframe>
                            </div>
                        @else
                            <div class="w-full bg-slate-50 rounded-2xl p-4 border border-slate-200 flex justify-center items-center">
                                <img src="{{ route('knowledge-base.attachment', $resource->id) }}" class="max-w-full max-h-[500px] rounded-xl shadow-sm object-contain" alt="{{ $resource->attachment_name }}">
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
                            <a href="{{ route('knowledge-base.attachment', $resource->id) }}?download=1" data-turbo="false"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 border border-emerald-200 text-emerald-700 hover:text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">
                                <i class="fa-solid fa-download"></i> {{ __('Download Original') }}
                            </a>
                        </div>
                        @if(strtolower($resource->format) === 'pdf')
                            <div class="w-full bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 shadow-inner">
                                <iframe src="{{ route('knowledge-base.attachment', $resource->id) }}" class="w-full h-[600px] border-none" allow="autoplay"></iframe>
                            </div>
                        @else
                            <div class="w-full bg-slate-50 rounded-2xl p-4 border border-slate-200 flex justify-center items-center">
                                <img src="{{ route('knowledge-base.attachment', $resource->id) }}" class="max-w-full max-h-[500px] rounded-xl shadow-sm object-contain" alt="{{ $resource->attachment_name }}">
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
                            <a href="{{ route('knowledge-base.attachment', $resource->id) }}" data-turbo="false"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 hover:bg-emerald-600 border border-emerald-200 text-emerald-700 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">
                                <i class="fa-solid fa-cloud-arrow-down"></i>
                                {{ __('Download Attachment') }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center bg-slate-50 rounded-2xl border border-slate-100 p-6 max-w-2xl mx-auto">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-indigo-100/50">
                            <i class="fa-solid fa-paperclip text-lg"></i>
                        </div>
                        <h3 class="text-slate-950 font-bold text-sm tracking-tight">
                            {{ app()->getLocale() == 'id' ? 'Dokumen Lampiran Saja' : 'Attachment-Only Document' }}
                        </h3>
                        <p class="text-xs text-slate-500 font-medium leading-relaxed mt-2">
                            {{ app()->getLocale() == 'id' 
                                ? 'Aset ini tidak memiliki isi teks artikel online. Silakan unduh dokumen lampiran asli di bawah untuk melihat detail lengkap.' 
                                : 'This asset does not contain any online article text. Please download the original attachment file below to view its full details.' }}
                        </p>
                    </div>
                @endif
            @endif
        </div>

        {{-- Premium Call to Action Footer Card --}}
        @if($resource->is_system)
            <div class="px-6 md:px-8 py-6 bg-slate-50 border-t border-slate-100">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-500 text-white rounded-xl flex items-center justify-center text-lg shadow-lg shadow-indigo-500/20 shrink-0">
                            <i class="{{ $iconClass }}"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 text-xs tracking-tight leading-none uppercase">{{ __('Compliance Resource Document') }}</h4>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                {{ __('Format') }}: {{ $resource->format ? strtoupper($resource->format) : 'PDF' }} • {{ __('ISO 27001:2022 Certified content') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if(filled($resource->attachment_path))
                            <a href="{{ route('knowledge-base.attachment', $resource->id) }}" data-turbo="false"
                               class="px-3.5 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-600 hover:text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-sm flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-paperclip"></i>
                                {{ __('Attachment') }} ({{ $resource->attachment_name }})
                            </a>
                        @endif
                        <a href="{{ route('knowledge-base.download', $resource->id) }}" data-turbo="false"
                           class="px-4 py-2 bg-slate-900 text-white hover:bg-indigo-600 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-md flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-cloud-arrow-down"></i>
                            {{ __('Download PDF') }}
                        </a>
                    </div>
                </div>
                
                <div class="text-center mt-5 text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>{{ __('ISO 27001:2022 Compliance Knowledge Base') }}</span>
                </div>
            </div>
        @endif
    </article>
</div>
@endsection
