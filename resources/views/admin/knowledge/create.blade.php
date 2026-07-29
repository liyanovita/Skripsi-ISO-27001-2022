@extends('layouts.admin')

@section('title', 'Add Knowledge Base Document')
@section('header_title', 'Add Knowledge Base Document')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .form-input { transition: border-color 0.15s, box-shadow 0.15s; }
    .form-input:focus { box-shadow: 0 0 0 3px rgba(37,99,235,0.12); border-color: #60a5fa; outline: none; background: #fff; }
    .section-card { background: #fff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 1px 4px 0 rgba(30,58,138,0.04); }
    .ql-toolbar.ql-snow {
        background: #f8fafc;
        border-color: #e2e8f0;
        border-radius: 0.75rem 0.75rem 0 0;
        padding: 8px 10px;
    }
    .ql-container.ql-snow {
        border-color: #e2e8f0;
        border-radius: 0 0 0.75rem 0.75rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .ql-editor { min-height: 380px; font-size: 14px; color: #334155; padding: 16px; }
    .ql-editor.ql-blank::before { color: #94a3b8; font-style: italic; }
</style>
@endpush

@section('content')
<div class="max-w-4xl">

    {{-- Back Link --}}
    <a href="{{ route('admin.knowledge.index') }}"
        class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-blue-600 transition-colors mb-5 font-medium group">
        <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Back to Knowledge Base
    </a>

    {{-- Page Title --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/20">
            <i class="fa-solid fa-plus"></i>
        </div>
        <div>
            <h2 class="text-lg font-black text-slate-800">Add New Document or Article</h2>
            <p class="text-xs text-slate-400 font-medium">Create guidance articles, policy templates, SOPs using Word-like editor.</p>
        </div>
    </div>

    <form id="kb-form" method="POST" action="{{ route('admin.knowledge.store') }}" enctype="multipart/form-data" class="space-y-5" x-data="{ categoryVal: @js(old('category', '')) }">
        @csrf

        {{-- Basic Information --}}
        <div class="section-card p-6 space-y-5">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-file-signature text-blue-500"></i> Document Metadata
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Title --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Document Title <span class="text-red-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        placeholder="e.g. ISO 27001:2022 Access Control Policy Template"
                        class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('title') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Category <span class="text-red-400">*</span></label>
                    <select name="category" required x-model="categoryVal"
                        class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm cursor-pointer">
                        <option value="" disabled selected hidden>— Select Category —</option>
                        <option value="guides">Implementation Guides</option>
                        <option value="templates">Policy Templates</option>
                        <option value="sop">Standard Operating Procedures (SOP)</option>
                        <option value="evidence">Evidence Examples</option>
                    </select>
                    @error('category') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Short Description --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Short Summary / Description <span class="text-red-400">*</span></label>
                <textarea name="description" rows="2" required
                    placeholder="Brief overview of what this document or policy covers…"
                    class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none">{{ old('description') }}</textarea>
                @error('description') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
            </div>
        </div>

        {{-- File Attachment Card --}}
        <div class="section-card p-6 space-y-4">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-paperclip text-blue-500"></i> File Attachment <span class="text-[9px] font-medium text-slate-300 normal-case tracking-normal">Optional</span>
            </h3>

            <label for="file-upload" class="flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-2xl bg-slate-50/60 hover:bg-blue-50/30 hover:border-blue-300 transition-all cursor-pointer group">
                <div class="space-y-2 text-center">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                    </div>
                    <div class="text-xs text-slate-600">
                        <span class="font-bold text-blue-600">Click to upload a file</span> or drag and drop
                        <input id="file-upload" name="attachment" type="file" class="sr-only" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.csv,.txt,.md">
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">Supports PDF, DOCX, XLSX, JPG, PNG, CSV, MD up to 10MB</p>
                </div>
            </label>
            <div id="file-name-display" class="text-xs font-bold text-emerald-600 hidden flex items-center gap-1.5 bg-emerald-50 px-3 py-2 rounded-xl border border-emerald-100">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <span id="file-name-text"></span>
            </div>
            @error('attachment') <p class="text-xs text-red-500 mt-1 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
        </div>

        {{-- Content — Quill Editor Card --}}
        <div class="section-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-pen-nib text-blue-500"></i> Article Content & Policy Text
                </h3>
                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">Rich Text & PDF Compiled</span>
            </div>

            <textarea name="content" id="content-input" class="hidden">{!! old('content') !!}</textarea>

            <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                <div id="quill-editor"></div>
            </div>
            @error('content') <p class="text-xs text-red-500 mt-1 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
        </div>

        {{-- Live Content Preview Card --}}
        <div class="section-card p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-eye text-blue-500"></i> Live Content Preview
                </h3>
                <span class="text-[10px] font-bold text-slate-400">Real-time Rendering</span>
            </div>
            <div id="content-preview" class="prose prose-slate max-w-none min-h-32 bg-slate-50/80 border border-slate-200/80 rounded-xl p-4 text-sm text-slate-700">
                {!! old('content') ?: '<p class="text-slate-400 italic">Start typing in the editor above to preview this resource content live…</p>' !!}
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-between items-center pt-2">
            <a href="{{ route('admin.knowledge.index') }}"
                class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit" id="submit-btn"
                class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-md shadow-blue-600/20 flex items-center gap-2">
                <i class="fa-solid fa-paper-plane text-xs"></i> Publish Document
            </button>
        </div>

    </form>
</div>

<script>
function initQuillEditor() {
    var editorEl = document.getElementById('quill-editor');
    if (!editorEl || editorEl._quillInstance) return;

    if (typeof Quill === 'undefined') {
        var s = document.createElement('script');
        s.src = 'https://cdn.quilljs.com/1.3.7/quill.min.js';
        s.onload = function() { initQuillEditor(); };
        document.head.appendChild(s);
        return;
    }

    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Enter full document text, SOP instructions, or policy content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['blockquote', 'code-block'],
                ['link'],
                ['clean']
            ]
        }
    });

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
                : '<p class="text-slate-400 italic">Start typing in the editor above to preview this resource content live…</p>';
        }
    });

    document.getElementById('kb-form').addEventListener('submit', function() {
        document.getElementById('content-input').value = quill.root.innerHTML;
    });

    var fileUpload = document.getElementById('file-upload');
    if (fileUpload) {
        fileUpload.addEventListener('change', function() {
            var display = document.getElementById('file-name-display');
            var text = document.getElementById('file-name-text');
            if (display && text) {
                if (this.files[0]) {
                    text.textContent = 'Selected: ' + this.files[0].name;
                    display.classList.remove('hidden');
                } else {
                    display.classList.add('hidden');
                }
            }
        });
    }

    editorEl._quillInstance = quill;
}

document.addEventListener('turbo:load', initQuillEditor);
if (document.readyState !== 'loading') { initQuillEditor(); }
</script>
@endsection
