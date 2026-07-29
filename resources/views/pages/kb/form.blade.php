@extends('layouts.app')
@section('title', isset($resource) ? 'Edit Knowledge Base Resource' : 'Add Knowledge Base Resource')
@section('view_name', isset($resource) ? 'Edit Knowledge Base Resource' : 'Add Knowledge Base Resource')

@if(auth()->user()->isAdmin())
@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow {
        background: #f8fafc;
        border-color: #e2e8f0;
        border-radius: 0.75rem 0.75rem 0 0;
        padding: 8px 10px;
        flex-wrap: wrap;
    }
    .ql-container.ql-snow {
        border-color: #e2e8f0;
        border-radius: 0 0 0.75rem 0.75rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .ql-editor {
        min-height: 400px;
        font-size: 14px;
        color: #334155;
        padding: 16px;
    }
    .ql-editor.ql-blank::before { color: #94a3b8; font-style: italic; }
    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="10px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="10px"]::before { content: '10px'; }
    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="12px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="12px"]::before { content: '12px'; }
    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="14px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="14px"]::before { content: '14px'; }
    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="16px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="16px"]::before { content: '16px'; }
    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="18px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="18px"]::before { content: '18px'; }
    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="24px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="24px"]::before { content: '24px'; }
    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="36px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="36px"]::before { content: '36px'; }
    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="48px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="48px"]::before { content: '48px'; }
    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="Arial"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="Arial"]::before { content: 'Arial'; font-family: Arial; }
    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="Georgia"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="Georgia"]::before { content: 'Georgia'; font-family: Georgia; }
    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="Verdana"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="Verdana"]::before { content: 'Verdana'; font-family: Verdana; }
    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="TimesNewRoman"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="TimesNewRoman"]::before { content: 'Times New Roman'; font-family: 'Times New Roman'; }
    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="CourierNew"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="CourierNew"]::before { content: 'Courier New'; font-family: 'Courier New'; }
    .ql-font-Arial { font-family: Arial, sans-serif; }
    .ql-font-Georgia { font-family: Georgia, serif; }
    .ql-font-Verdana { font-family: Verdana, sans-serif; }
    .ql-font-TimesNewRoman { font-family: 'Times New Roman', serif; }
    .ql-font-CourierNew { font-family: 'Courier New', monospace; }
</style>
@endpush
@endif

@section('content')
<div class="w-full pb-12">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center text-white shadow-lg shadow-slate-800/20">
                    <i class="fa-solid fa-file-pen text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter uppercase">{{ isset($resource) ? __('Edit Knowledge Base Resource') : __('Add New Knowledge Base Resource') }}</h1>
                    <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px] mt-1">{{ __('Knowledge Base Management') }}</p>
                </div>
            </div>
            <a href="{{ route('knowledge-base.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i> {{ __('Back') }}
            </a>
        </div>

        {{-- Form --}}
        <div class="px-8 py-6">
            <form id="kb-form" action="{{ isset($resource) ? route('knowledge-base.update', $resource->id) : route('knowledge-base.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @if(isset($resource))
                    @method('PUT')
                @endif

                <!-- Guidance Info Banner -->
                @if(auth()->user()->isAdmin())
                <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-2xl flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/50">
                        <i class="fa-solid fa-circle-info text-sm"></i>
                    </div>
                    <div class="text-xs">
                        <p class="font-bold text-slate-800 uppercase tracking-wider mb-1">
                            {{ app()->getLocale() == 'id' ? 'Panduan Pengisian Konten' : 'Content Entry Guidance' }}
                        </p>
                        <p class="text-slate-500 leading-relaxed font-medium">
                            {{ app()->getLocale() == 'id'
                                ? 'Tulis konten lengkap pada editor di bawah jika ingin menampilkan dokumen sebagai artikel online interaktif dan diekspor ke format PDF. Jika Anda hanya ingin mengunggah dokumen yang sudah jadi (seperti Word, Excel, CSV, atau PDF) untuk diunduh langsung oleh pengguna, cukup unggah file tersebut di bagian lampiran di bawah dan tulis ringkasan singkat pada kolom editor.'
                                : 'Write the complete text in the editor below if you want the resource to display as a fully-formatted online article and export directly to PDF. If you only wish to submit a pre-made document (Word, Excel, CSV, PDF, etc.) for direct download, simply upload it as an attachment below and write a brief summary in the content field.' }}
                        </p>
                    </div>
                </div>
                @endif

                {{-- Title --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Resource Title') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $resource->title ?? '') }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm"
                        placeholder="{{ __('e.g. Password Policy 2026') }}">
                    @error('title') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Category --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Resource Category') }} <span class="text-rose-500">*</span></label>
                    <select name="category" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm cursor-pointer">
                        <option value="" disabled {{ !old('category', $resource->category ?? '') ? 'selected' : '' }}>{{ __('Select a category...') }}</option>
                        <option value="guides" {{ old('category', $resource->category ?? '') === 'guides' ? 'selected' : '' }}>{{ __('Guides') }}</option>
                        <option value="templates" {{ old('category', $resource->category ?? '') === 'templates' ? 'selected' : '' }}>{{ __('Templates') }}</option>
                        <option value="sop" {{ old('category', $resource->category ?? '') === 'sop' ? 'selected' : '' }}>{{ __('SOP') }}</option>
                        <option value="evidence" {{ old('category', $resource->category ?? '') === 'evidence' ? 'selected' : '' }}>{{ __('Evidence') }}</option>
                    </select>
                    @error('category') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Short Description') }}</label>
                    <textarea name="description" rows="2"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm resize-y"
                        placeholder="{{ __('Brief summary of this document...') }}">{{ old('description', $resource->description ?? '') }}</textarea>
                    @error('description') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Attachment --}}
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 space-y-3">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Original Attachment') }}</label>
                            <p class="text-xs text-slate-500 font-medium mt-1">{{ __('Optional. Upload the source file if users should download DOCX, XLSX, PDF, TXT, MD, or CSV directly.') }}</p>
                        </div>
                        @if(isset($resource) && $resource->attachment_name)
                            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-600">
                                <i class="fa-solid fa-paperclip text-blue-500"></i>
                                {{ $resource->attachment_name }}
                            </span>
                        @endif
                    </div>
                    <input type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.md,.csv"
                        class="block w-full text-xs font-bold text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2.5 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:text-white hover:file:bg-blue-600">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Max 10 MB. Uploading a new attachment replaces the existing file.') }}</p>
                    @error('attachment') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                @if(auth()->user()->isAdmin())
                {{-- Content --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-between">
                        <span>{{ __('Knowledge Base Content') }}</span>
                        <span class="text-blue-500 font-bold">{{ __('Word-like editor (will be exported to PDF)') }}</span>
                    </label>

                    {{-- PDF Generation Warning Banner --}}
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-circle-exclamation text-xs"></i>
                        </div>
                        <div class="text-[11px] leading-normal font-medium text-amber-900">
                            <span class="font-bold">{{ __('PDF Export Notice:') }}</span> {{ __('The content written in this editor will be compiled directly into the official PDF download. Please ensure alignment, lists, and tables are structured neatly for a professional printout.') }}
                        </div>
                    </div>

                    {{-- Hidden textarea for form submission --}}
                    <textarea name="content" id="content-input" class="hidden">{!! old('content', $resource->content ?? '') !!}</textarea>

                    {{-- Quill Editor --}}
                    <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                        <div id="quill-editor"></div>
                    </div>
                    @error('content') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Content Preview') }}</label>
                        <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest">{{ __('Live preview') }}</span>
                    </div>
                    <div id="content-preview" class="prose prose-sm prose-slate max-w-none min-h-32 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700">
                        {!! old('content', $resource->content ?? '') ?: '<p class="text-slate-400 italic">' . __('Start typing to preview this resource...') . '</p>' !!}
                    </div>
                </div>
                @endif

                {{-- Submit --}}
                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-8 py-3 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg active:scale-95 flex items-center gap-2">
                        <i class="fa-solid {{ isset($resource) ? 'fa-save' : 'fa-plus' }}"></i>
                        {{ isset($resource) ? __('Update Resource') : __('Save Resource') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin())
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function() {
    // Register custom fonts
    var Font = Quill.import('formats/font');
    Font.whitelist = ['Arial', 'Georgia', 'Verdana', 'TimesNewRoman', 'CourierNew'];
    Quill.register(Font, true);

    // Register custom font sizes
    var Size = Quill.import('attributors/style/size');
    Size.whitelist = ['10px', '12px', '14px', '16px', '18px', '24px', '36px', '48px'];
    Quill.register(Size, true);

    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: '{{ __("Enter the full policy text, SOP steps, or guide content here...") }}',
        modules: {
            toolbar: [
                [{ 'font': ['Arial', 'Georgia', 'Verdana', 'TimesNewRoman', 'CourierNew'] }],
                [{ 'size': ['10px', '12px', '14px', '16px', '18px', '24px', '36px', '48px'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }, { 'align': [] }],
                ['blockquote', 'code-block'],
                ['link'],
                ['clean']
            ]
        }
    });

    // Pre-fill with existing content
    var existingContent = document.getElementById('content-input').value;
    if (existingContent && existingContent.trim()) {
        quill.root.innerHTML = existingContent;
    }

    // Live preview sync
    var preview = document.getElementById('content-preview');
    quill.on('text-change', function() {
        var html = quill.root.innerHTML;
        var text = quill.getText().trim();
        document.getElementById('content-input').value = html;
        if (preview) {
            preview.innerHTML = text
                ? html
                : '<p class="text-slate-400 italic">{{ __("Start typing to preview this resource...") }}</p>';
        }
    });

    // Sync to hidden textarea before form submit
    document.getElementById('kb-form').addEventListener('submit', function() {
        document.getElementById('content-input').value = quill.root.innerHTML;
    });
})();
</script>
@endif
@endsection
