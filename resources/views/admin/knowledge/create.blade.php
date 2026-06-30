@extends('layouts.admin')

@section('title', __('Create Knowledge Base Item'))
@section('header_title', __('Create Knowledge Base Item'))

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
<div class="mb-6">
    <a href="{{ route('admin.knowledge.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> {{ __('Back to Knowledge Base') }}
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden max-w-5xl" x-data="{
    content: @js(old('content', '')),
    categoryVal: @js(old('category', '')),
    previewHtml: '',
    easyMDE: null,
    init() {
        this.refreshPreview();
        const textarea = document.getElementById('content-textarea');
        if (textarea && typeof ClassicEditor !== 'undefined') {
            class Base64UploadAdapter {
                constructor(loader) {
                    this.loader = loader;
                }
                upload() {
                    return this.loader.file
                        .then(file => new Promise((resolve, reject) => {
                            const reader = new FileReader();
                            reader.onload = () => {
                                resolve({ default: reader.result });
                            };
                            reader.onerror = error => {
                                reject(error);
                            };
                            reader.readAsDataURL(file);
                        }));
                }
                abort() {}
            }

            ClassicEditor
                .create(textarea, {
                    placeholder: @js(__('Enter the full policy text, SOP steps, or guide content here...')),
                    extraPlugins: [
                        function(editor) {
                            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                                return new Base64UploadAdapter(loader);
                            };
                        }
                    ],
                    toolbar: [
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                        'blockQuote', 'insertTable', 'uploadImage', '|',
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
        this.previewHtml = this.content || '<p class=\'text-slate-400 italic\'>' + @js(__('Start typing to preview this resource...')) + '</p>';
    }
}">
    <div class="p-6 border-b border-slate-200 bg-slate-50">
        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ __('Add New Document or Article') }}</h2>
        <p class="text-xs text-slate-500 mt-1 uppercase tracking-widest font-bold">{{ __('Knowledge Base Administration') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.knowledge.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf

        {{-- Title & Category --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Title --}}
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Title') }} <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="{{ __('e.g. ISO 27001 Internal Audit Guide') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm" required>
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Category --}}
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Category') }} <span class="text-red-500">*</span></label>
                <select name="category" required x-model="categoryVal"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm cursor-pointer"
                    :class="categoryVal === '' ? 'text-slate-400' : 'text-slate-700'">
                    <option value="" disabled selected hidden class="text-slate-400">-- {{ __('Select Category') }} --</option>
                    <option value="guides" class="text-slate-700">{{ __('Implementation Guides') }}</option>
                    <option value="templates" class="text-slate-700">{{ __('Policy Templates') }}</option>
                    <option value="sop" class="text-slate-700">{{ __('Standard Operating Procedures') }}</option>
                    <option value="evidence" class="text-slate-700">{{ __('Evidence Examples') }}</option>
                </select>
                @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Description --}}
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Short Description') }} <span class="text-red-500">*</span></label>
            <textarea name="description" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm resize-y" placeholder="{{ __('Brief summary of this document...') }}" required>{{ old('description') }}</textarea>
            @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- File Attachment --}}
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 space-y-3">
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('File Attachment') }}</label>
                <p class="text-xs text-slate-500 font-medium mt-1">{{ __('Optional. Upload the source file if users should download DOCX, XLSX, PDF, TXT, MD, or CSV directly.') }}</p>
            </div>
            <label for="file-upload" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-2xl bg-white relative group hover:border-blue-400 transition-colors cursor-pointer block">
                <div class="space-y-1 text-center">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                    <div class="flex text-sm text-slate-600 justify-center">
                        <span class="font-bold text-blue-600 group-hover:text-blue-500 transition-colors">{{ __('Upload a file') }}</span>
                        <input id="file-upload" name="attachment" type="file" class="sr-only" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.csv,.txt,.md">
                        <p class="pl-1">{{ __('or drag and drop') }}</p>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">{{ __('PDF, DOCX, XLSX, JPG, CSV up to 10MB') }}</p>
                </div>
            </label>
            <div id="file-name-display" class="text-xs font-bold text-emerald-600 hidden"></div>
            @error('attachment') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
        </div>

        {{-- Content --}}
        <div class="space-y-2">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-between">
                <span>{{ __('Knowledge Base Content') }}</span>
                <span class="text-indigo-500 font-bold">{{ __('Word-like editor (will be exported to PDF)') }}</span>
            </label>

            {{-- PDF Generation Warning Banner --}}
            <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-exclamation text-xs"></i>
                </div>
                <div class="text-[11px] leading-normal font-medium text-amber-900">
                    <span class="font-bold">{{ __('PDF Export Notice:') }}</span> {{ __('The content written in this editor will be compiled directly into the official PDF download. Please ensure alignment, lists, and tables are structured neatly for a professional printout.') }}
                </div>
            </div>

            <textarea id="content-textarea" name="content" rows="12" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-mono text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm resize-y custom-scrollbar" placeholder="{{ __('Enter the full policy text, SOP steps, or guide content here...') }}">{{ old('content') }}</textarea>
            @error('content') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Preview --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Content Preview') }}</label>
                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">{{ __('Live preview') }}</span>
            </div>
            <div class="prose prose-sm prose-slate max-w-none min-h-32 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700" x-html="previewHtml"></div>
        </div>

        {{-- Submit buttons --}}
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
            <a href="{{ route('admin.knowledge.index') }}" class="px-6 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors text-xs uppercase tracking-wider">{{ __('Cancel') }}</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all hover:scale-[1.02] active:scale-[0.98] text-xs uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-upload"></i> {{ __('Publish Item') }}
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('file-upload').addEventListener('change', function(e) {
        const display = document.getElementById('file-name-display');
        if (this.files && this.files[0]) {
            display.textContent = @js(__('Selected file:')) + ' ' + this.files[0].name;
            display.classList.remove('hidden');
        } else {
            display.classList.add('hidden');
        }
    });
</script>
@endsection
