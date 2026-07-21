@extends('layouts.admin')

@section('title', __('Create Knowledge Base Item'))
@section('header_title', __('Create Knowledge Base Item'))

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    #quill-editor {
        min-height: 380px;
        font-size: 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #334155;
        background: #fff;
    }
    .ql-toolbar.ql-snow {
        border-radius: 0.75rem 0.75rem 0 0;
        border-color: #e2e8f0;
        background: #f8fafc;
        padding: 10px 8px;
    }
    .ql-container.ql-snow {
        border-radius: 0 0 0.75rem 0.75rem;
        border-color: #e2e8f0;
        background: #fff;
    }
    .ql-toolbar.ql-snow .ql-formats { margin-right: 10px; }
    .ql-editor.ql-blank::before { color: #94a3b8; font-style: italic; }
    .ql-snow .ql-picker-label { color: #334155; }
</style>
@endpush

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.knowledge.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> {{ __('Back to Knowledge Base') }}
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden max-w-5xl" x-data="{ categoryVal: @js(old('category', '')) }">
    <div class="p-6 border-b border-slate-200 bg-slate-50">
        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ __('Add New Document or Article') }}</h2>
        <p class="text-xs text-slate-500 mt-1 uppercase tracking-widest font-bold">{{ __('Knowledge Base Administration') }}</p>
    </div>

    <form id="kb-form" method="POST" action="{{ route('admin.knowledge.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf

        {{-- Title & Category --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Title') }} <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="{{ __('e.g. ISO 27001 Internal Audit Guide') }}"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm" required>
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Category') }} <span class="text-red-500">*</span></label>
                <select name="category" required x-model="categoryVal"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm cursor-pointer"
                    :class="categoryVal === '' ? 'text-slate-400' : 'text-slate-700'">
                    <option value="" disabled selected hidden>-- {{ __('Select Category') }} --</option>
                    <option value="guides">{{ __('Implementation Guides') }}</option>
                    <option value="templates">{{ __('Policy Templates') }}</option>
                    <option value="sop">{{ __('Standard Operating Procedures') }}</option>
                    <option value="evidence">{{ __('Evidence Examples') }}</option>
                </select>
                @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Description --}}
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Short Description') }} <span class="text-red-500">*</span></label>
            <textarea name="description" rows="2"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm resize-y"
                placeholder="{{ __('Brief summary of this document...') }}" required>{{ old('description') }}</textarea>
            @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- File Attachment --}}
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 space-y-3">
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('File Attachment') }}</label>
                <p class="text-xs text-slate-500 font-medium mt-1">{{ __('Optional. Upload the source file if users should download DOCX, XLSX, PDF, TXT, MD, or CSV directly.') }}</p>
            </div>
            <label for="file-upload" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-2xl bg-white group hover:border-blue-400 transition-colors cursor-pointer">
                <div class="space-y-1 text-center">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                    <div class="flex text-sm text-slate-600 justify-center">
                        <span class="font-bold text-blue-600 group-hover:text-blue-500">{{ __('Upload a file') }}</span>
                        <input id="file-upload" name="attachment" type="file" class="sr-only" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.csv,.txt,.md">
                        <p class="pl-1">{{ __('or drag and drop') }}</p>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">PDF, DOCX, XLSX, JPG, CSV up to 10MB</p>
                </div>
            </label>
            <div id="file-name-display" class="text-xs font-bold text-emerald-600 hidden"></div>
            @error('attachment') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        {{-- Content — Quill Editor --}}
        <div class="space-y-2">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-between">
                <span>{{ __('Knowledge Base Content') }}</span>
                <span class="text-indigo-500 font-bold">{{ __('Word-like editor (will be exported to PDF)') }}</span>
            </label>

            <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-exclamation text-xs"></i>
                </div>
                <div class="text-[11px] leading-normal font-medium text-amber-900">
                    <span class="font-bold">{{ __('PDF Export Notice:') }}</span> {{ __('The content written in this editor will be compiled directly into the official PDF download. Please ensure alignment, lists, and tables are structured neatly for a professional printout.') }}
                </div>
            </div>

            {{-- Quill Editor Container --}}
            <div class="rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div id="quill-editor">{!! old('content') !!}</div>
            </div>

            {{-- Hidden input yang akan diisi oleh Quill sebelum submit --}}
            <input type="hidden" name="content" id="content-input">
            @error('content') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Preview --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Content Preview') }}</label>
                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">{{ __('Live preview') }}</span>
            </div>
            <div id="content-preview" class="prose prose-sm prose-slate max-w-none min-h-32 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700">
                <p class="text-slate-400 italic">{{ __('Start typing to preview this resource...') }}</p>
            </div>
        </div>

        {{-- Submit buttons --}}
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
            <a href="{{ route('admin.knowledge.index') }}" class="px-6 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors text-xs uppercase tracking-wider">{{ __('Cancel') }}</a>
            <button type="submit" id="submit-btn" class="px-6 py-2.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all hover:scale-[1.02] active:scale-[0.98] text-xs uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-upload"></i> {{ __('Publish Item') }}
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
<script>
// File upload display
document.getElementById('file-upload').addEventListener('change', function() {
    const display = document.getElementById('file-name-display');
    display.textContent = this.files[0] ? '{{ __("Selected file:") }} ' + this.files[0].name : '';
    display.classList.toggle('hidden', !this.files[0]);
});

// Inisialisasi Quill Editor
const quill = new Quill('#quill-editor', {
    theme: 'snow',
    placeholder: '{{ __("Enter the full policy text, SOP steps, or guide content here...") }}',
    modules: {
        toolbar: [
            [{ 'font': [] }, { 'size': ['small', false, 'large', 'huge'] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'script': 'sub' }, { 'script': 'super' }],
            ['blockquote', 'code-block'],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }, { 'align': [] }],
            ['link', 'image'],
            ['clean']
        ]
    }
});

// Sync ke preview secara live
const preview = document.getElementById('content-preview');
quill.on('text-change', function() {
    const html = quill.root.innerHTML;
    document.getElementById('content-input').value = html;
    preview.innerHTML = quill.getText().trim()
        ? html
        : '<p class="text-slate-400 italic">{{ __("Start typing to preview this resource...") }}</p>';
});

// Salin HTML ke hidden input sebelum form di-submit
document.getElementById('kb-form').addEventListener('submit', function() {
    document.getElementById('content-input').value = quill.root.innerHTML;
});
</script>
@endsection
