@extends('layouts.app')
@section('title', isset($template) ? 'Edit Asset' : 'Share Asset')
@section('view_name', isset($template) ? 'Edit Asset' : 'Share Asset')

@push('styles')
<style>
    .ck-editor__editable_inline {
        min-height: 350px !important;
        border-color: #e2e8f0 !important;
        border-bottom-left-radius: 0.75rem !important;
        border-bottom-right-radius: 0.75rem !important;
        background-color: #f8fafc !important;
        color: #334155 !important;
    }
    .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
        border-color: #e2e8f0 !important;
    }
    .ck.ck-editor__main>.ck-editor__editable.ck-focused {
        border-color: #94a3b8 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(30, 41, 59, 0.05) !important;
    }
    .ck.ck-toolbar {
        border-color: #e2e8f0 !important;
        border-top-left-radius: 0.75rem !important;
        border-top-right-radius: 0.75rem !important;
        background-color: #f8fafc !important;
    }
</style>
@endpush

@push('head_scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
@endpush

@section('content')
<div class="w-full pb-12" x-data="{
    content: @js(old('content', $template->content ?? '')),
    tags: @js(old('tags', isset($template) ? implode(', ', $template->tags ?? []) : '')),
    previewHtml: '',
    easyMDE: null,
    init() {
        this.refreshPreview();
        
        const textarea = document.getElementById('content-textarea');
        if (textarea && typeof ClassicEditor !== 'undefined') {
            ClassicEditor
                .create(textarea, {
                    placeholder: @js(__('Enter your detailed guide, best practices, or policy text here...')),
                    toolbar: [
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                        'blockQuote', 'insertTable', '|',
                        'undo', 'redo'
                    ]
                })
                .then(editor => {
                    this.easyMDE = editor;
                    editor.setData(this.content || '');
                    
                    editor.model.document.on('change:data', () => {
                        this.content = editor.getData();
                        this.refreshPreview();
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        }
    },
    refreshPreview() {
        this.previewHtml = this.content || '<p class=\'text-slate-400 italic\'>' + @js(__('Start typing to preview your asset...')) + '</p>';
    }
}">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        
        {{-- Header --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter uppercase">{{ isset($template) ? __('Edit Community Asset') : __('Share New Asset') }}</h1>
                    <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px] mt-1">{{ __('Community Collaboration') }}</p>
                </div>
            </div>
            <a href="{{ route('community.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i> {{ __('Back') }}
            </a>
        </div>

        {{-- Form --}}
        <div class="px-8 py-6">
            <form action="{{ isset($template) ? route('community.update', $template->id) : route('community.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" @submit="if(typeof tinymce !== 'undefined') { tinymce.triggerSave(); }">
                @csrf
                @if(isset($template))
                    @method('PUT')
                @endif

                {{-- Title & Tags --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Asset Title') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $template->title ?? '') }}" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-400 transition-all shadow-sm"
                            placeholder="{{ __('e.g. Fintech Cloud Policy Blueprint') }}">
                        @error('title') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Tags (Comma Separated)') }}</label>
                        <input type="text" name="tags" x-model="tags"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-400 transition-all shadow-sm"
                            placeholder="{{ __('e.g. Fintech, AWS, Remote Work') }}">
                        @error('tags') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Short Description') }}</label>
                    <textarea name="description" rows="2"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-400 transition-all shadow-sm resize-y"
                        placeholder="{{ __('Briefly describe the use-cases and coverage of this asset...') }}">{{ old('description', $template->description ?? '') }}</textarea>
                    @error('description') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Attachment --}}
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 space-y-3">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Upload Document') }}</label>
                            <p class="text-xs text-slate-500 font-medium mt-1">{{ __('Optional. Upload your JSON session, PDF, Word Document, or Excel spreadsheet to share with the community.') }}</p>
                        </div>
                        @if(isset($template) && $template->attachment_name)
                            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-600">
                                <i class="fa-solid fa-paperclip text-indigo-500"></i>
                                {{ $template->attachment_name }}
                            </span>
                        @endif
                    </div>
                    <input type="file" name="attachment" accept=".json,.pdf,.doc,.docx,.xls,.xlsx,.txt,.md,.csv"
                        class="block w-full text-xs font-bold text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2.5 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:text-white hover:file:bg-indigo-600">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Max 20 MB. Uploading a new attachment replaces the existing file.') }}</p>
                    @error('attachment') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Content --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-between">
                        <span>{{ __('Detailed Content / Article') }}</span>
                        <span class="text-indigo-500 font-bold">{{ __('Optional') }}</span>
                    </label>
                    <textarea id="content-textarea" name="content" rows="12"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-mono text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-400 transition-all shadow-sm resize-y custom-scrollbar"
                        placeholder="{{ __('Enter the full policy text, guide, or best practice article here...') }}">{{ old('content', $template->content ?? '') }}</textarea>
                    @error('content') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Content Preview') }}</label>
                        <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">{{ __('Live preview') }}</span>
                    </div>
                    <div class="prose prose-sm prose-slate max-w-none min-h-32 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700" x-html="previewHtml"></div>
                </div>

                {{-- Submit --}}
                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 text-white text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg active:scale-95 flex items-center gap-2">
                        <i class="fa-solid {{ isset($template) ? 'fa-save' : 'fa-share' }}"></i>
                        {{ isset($template) ? __('Update Asset') : __('Share Asset') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
